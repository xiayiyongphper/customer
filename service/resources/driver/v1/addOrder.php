<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 11:31
 */

namespace service\resources\driver\v1;

use common\components\UserTools;
use common\models\driver\Driver;
use common\models\driver\Order;
use common\models\LeCustomers;
use framework\components\barcode\Converter;
use service\components\Events;
use service\components\Proxy;
use service\components\Tools;
use service\message\common\Message;
use service\message\driver\DriverOrder;
use service\message\driver\OrderActionResponse;
use service\models\common\DriverAbstract;
use service\models\common\DriverException;

class addOrder extends DriverAbstract
{
    public $tips = [
        'canceled' => '订单已取消',
        'hold' => '订单已申请取消',
        'processing' => '供应商未接单',
        'closed' => '订单已关闭',
        'rejected_closed' => '超市已拒绝订单'
    ];

    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed
     */
    public function run($data)
    {
//        Tools::log('===========','wangyang.log');
        /** @var \service\message\driver\AddOrder $request */
        $request = self::parseRequest($data);
        $response = self::response();

        $driver = $this->initDriver($request);

        $increment_id = $request->getIncrementId();
        $scan_code = $request->getScanCode();

        $order = null;
        if ($increment_id) {
            //订单号
            $order = UserTools::getOrderInfoByProxy('increment_id', $increment_id);
        } else if ($scan_code) {
            //订单ID
            $order_id = Converter::get()->decode($scan_code);
            if ($order_id) {
                $order = UserTools::getOrderInfoByProxy('order_id', $order_id);
            } else {
                DriverException::orderNotExist();
            }
        } else {
            DriverException::orderNotExist();
        }

        //先判断是不是自己供应商的订单
        //4:该订单不属于您的供货商，无法添加到发货列表中
        if ($order && $driver->wholesaler_id != $order->getWholesalerId()) {
            //4:该订单不属于您的供货商，无法添加到发货列表中
            $message = $this->message('添加失败', '该订单不属于您的供货商，无法添加到发货列表中', 4);
            $response->setMessage($message);
            //Tools::log($response,'wangyang.log');
            return $response;
        }

        //4:订单状态为[订单已取消]，无法添加到发货列表中
        if (in_array($order->getStatus(), ['canceled', 'hold', 'processing', 'closed', 'rejected_closed'])) {
            $tip = $this->tips[$order->getStatus()];
            //3:该订单状态为[订单已取消]，无法添加到发货列表中
            $message = $this->message('添加失败', "该订单状态为[{$tip}]，无法添加到发货列表中", 3);
            $response->setMessage($message);
            //Tools::log($response,'wangyang.log');
            return $response;
        }

        /** @var Order $driverOrder */
        $driverOrder = Order::getOrderByIncrementId($order->getIncrementId());

        //订单属于该供应商，且订单不是（待供货商接单、已取消订单、申请取消订单）
        if ($driverOrder) {

            //2:该订单已送达
            if ($driverOrder->state == Order::STATE_COMPLETE) {
                $message = $this->message('订单已送达', '该订单已送达，请勿重复配送', 2);
                $response->setMessage($message);
                //Tools::log($response,'wangyang.log');
                return $response;
            } else {
                //未送达，正在发货订单
                if ($driverOrder->driver_id == $driver->entity_id) {
                    //1:该订单已在送货列表中，请勿重复添加
                    $message = $this->message('请勿重复添加', '该订单已在送货列表中，请勿重复添加', 1);
                    $response->setMessage($message);
                    //Tools::log($response,'wangyang.log');
                    return $response;
                } else {
                    if ($driverOrder->driver_id == 0) {
                        //添加订单
                    } else {
                        if ($request->getFlag() == 1) {
                            //我来送货
                            $this->updateOrder($driverOrder, $order, $driver);
                            $message = $this->message('转移成功', '订单转移成功', 0);
                            $response->setMessage($message);
                        } else {
                            //5:该订单已被[张师傅，1709897897]扫码，请勿重复添加
                            /** @var Driver $driver_exist */
                            $driver_exist = Driver::findOne(['entity_id' => $driverOrder->driver_id]);
                            $message_response = '该订单已被[' . $driver_exist->name . ',' . $driver_exist->phone . ']扫码，请勿重复添加';
                            $message = $this->message('已被其他司机扫码', $message_response, 5);
                            $response->setMessage($message);
                            //Tools::log($response,'wangyang.log');
                            return $response;
                        }

                    }
                }
            }

        } else {
            $this->createOrder($order, $driver);
            $message = $this->message('添加成功', '订单添加成功', 0);
            $response->setMessage($message);

            // 添加成功的时候通知core模块,core会给订单加状态
            $name = Events::EVENT_LOGISTICS_STATUS_CHANGE;
            $eventName = Events::getCoreEventName($name);
            $events[$eventName] = [
                'name' => $name,
                'data' => [
                    'driver_order' => $order->toArray(),
                    'driver' => $driver->toArray(),
                    'comment' => "供货商已发货，司机电话" . $driver->phone,
                ],
            ];
            foreach ($events as $eventName => $event) {
                Proxy::sendMessage($eventName, $event);
            }

        }

        /** @var Order $driverOrder */
        $driverOrder = Order::getOrderByIncrementId($order->getIncrementId());
        $driverOrderArray = [
            'driver_order_id' => $driverOrder->entity_id,
            'driver_id' => $driver->entity_id,
            'state' => $driverOrder->state,
            'status' => $driverOrder->status,
            'created_at' => $driverOrder->created_at,
        ];
        $driverOrderModel = new DriverOrder();
        $driverOrderModel->setFrom($driverOrderArray);
        $driverOrderModel->setOrder($order);
        $response->setOrder($driverOrderModel);
        //Tools::log($response,'wangyang.log');
        return $response;

    }

    /**
     * @param Order $driverOrder
     * @param \service\message\common\Order $order
     * @param $driver
     * @throws DriverException
     */
    public function updateOrder($driverOrder, $order, $driver)
    {
        $driverOrder->driver_id = $driver->entity_id;
        $driverOrder->order_id = $order->getOrderId();
        $driverOrder->increment_id = $order->getIncrementId();
        $driverOrder->updated_at = date('Y-m-d H:i:s');
        $driverOrder->setDriver($driver);
        $driverOrder->setState(Order::STATE_PROCESSING, Order::STATUS_PROCESSING);
        if (!$driverOrder->save()) {
            DriverException::driverSystemError();
        }
    }

    /**
     * @param \service\message\common\Order $order
     * @param Driver $driver
     * @throws DriverException
     */
    public function createOrder($order, $driver)
    {
        $driverOrder = new Order();
        /** @var LeCustomers $customer */
        $customer = LeCustomers::findByCustomerId($order->getCustomerId());
        if ($customer) {
            $driverOrder->lat = $customer->lat;
            $driverOrder->lng = $customer->lng;
        }

        $driverOrder->created_at = date('Y-m-d H:i:s');
        $driverOrder->driver_id = $driver->entity_id;
        $driverOrder->order_id = $order->getOrderId();
        $driverOrder->increment_id = $order->getIncrementId();
        $driverOrder->wholesaler_id = $order->getWholesalerId();
        $driverOrder->wholesaler_name = $order->getWholesalerName();
        $driverOrder->customer_id = $order->getCustomerId();
        $driverOrder->customer_name = $order->getCustomerName();
        $driverOrder->customer_phone = $order->getPhone();
        $driverOrder->updated_at = date('Y-m-d H:i:s');
        $driverOrder->driver_phone = $driver->phone;
        $driverOrder->driver_name = $driver->name;
        $driverOrder->setDriver($driver);
        $driverOrder->setState(Order::STATE_PROCESSING, Order::STATUS_PROCESSING);
        if (!$driverOrder->save()) {
            DriverException::driverSystemError();
        }
    }

    public function message($title, $text, $code)
    {
        $message = new Message();
        $message->setText($text);
        $message->setTitle($title);
        $message->setCode($code);
        return $message;
    }

    public static function request()
    {
        return new \service\message\driver\AddOrder();
    }

    public static function response()
    {
        return new OrderActionResponse();
    }
}