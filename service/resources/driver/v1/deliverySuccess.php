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
use framework\components\barcode\Converter;
use service\components\Events;
use service\components\Proxy;
use service\components\Tools;
use service\message\common\Message;
use service\message\driver\DriverOrder;
use service\message\driver\OrderAction;
use service\message\driver\OrderActionResponse;
use service\models\common\DriverAbstract;
use service\models\common\DriverException;

class deliverySuccess extends DriverAbstract
{
    /**
     * @param string $data
     * @return bool
     */
    public function run($data)
    {
        /** @var OrderAction $request */
        $request = self::parseRequest($data);
        $response = self::response();
        $condition = [];
        if ($request->getDriverOrderId() > 0) {
            $condition['entity_id'] = $request->getDriverOrderId();
        }

        if ($request->getOrderId() > 0) {
            $condition['order_id'] = $request->getOrderId();
        }

        if ($request->getIncrementId()) {
            $condition['increment_id'] = $request->getIncrementId();
        }

        if ($request->getScanCode()) {
            $orderId = Converter::get()->decode($request->getScanCode());
            if ($orderId > 0) {
                $condition['order_id'] = $orderId;
            }
        }
        if (count($condition) == 0) {
            DriverException::orderNotExist();
        }

        $condition['driver_id'] = $request->getDriverId();
        /** @var Order $order */
        $order = Order::findOne($condition);
        $message = new Message();
        try {
            if (!$order) {
                DriverException::orderNotInTheList();
            }
            $salesOrder = UserTools::getOrderInfoByProxy('order_id',$order->order_id);
            if ($salesOrder) {
                if ($salesOrder == 'canceled') {
                    DriverException::orderAlreadyCanceled();
                } else {
                    $order->deliverySuccess()->setLocation($request->getLat(), $request->getLng())->save();
                    $message->setCode(0);
                    $message->setTitle('送货成功');
                    $message->setText('送货成功，可在已送达页查看该订单！\n订单编号：' . $order->increment_id);
                }
            } else {
                DriverException::orderNotExist();
            }
            $driverOrder = new DriverOrder();
            $driverOrder->setDriverOrderId($order->entity_id);
            $driverOrder->setIncrementId($order->increment_id);
            $driverOrder->setOrderId($order->order_id);
            $driverOrder->setDriverId($order->driver_id);
            $driverOrder->setCustomerName($order->customer_name);
            $driverOrder->setCustomerId($order->customer_id);
            $driverOrder->setCustomerPhone($order->customer_phone);
            $driverOrder->setWholesalerId($order->wholesaler_id);
            $driverOrder->setWholesalerName($order->wholesaler_name);
            $driverOrder->setState($order->state);
            $driverOrder->setStatus($order->status);
            $driverOrder->setStatusLabel($order->getStatusLabel());
            $driverOrder->setCreatedAt($order->created_at);
            $driverOrder->setDeliveryTime($order->delivery_time);
            $driverOrder->setCompletedAt($order->completed_at);
            $driverOrder->setLat($order->lat);
            $driverOrder->setLng($order->lng);
            $response->setOrder($driverOrder);

			// 送达成功的时候通知core模块,core会给订单加状态
			//$driver = Driver::find()->where(['entity_id' => $request->getDriverId()])->one();
			$name = Events::EVENT_LOGISTICS_STATUS_CHANGE;
			$eventName = Events::getCoreEventName($name);
			$events[$eventName] = [
				'name' => $name,
				'data' => [
					'driver_order' => $order->toArray(),
					//'driver'=> $driver->toArray(),
					'comment' => '供货商已送达',
				],
			];
			foreach ($events as $eventName => $event) {
				Proxy::sendMessage($eventName, $event);
			}

        } catch (\Exception $e) {
            $message->setCode($e->getCode());
            $message->setTitle('送达失败');
            $message->setText($e->getMessage());
        }
        $response->setMessage($message);
        return $response;
    }

    public static function request()
    {
        return new OrderAction();
    }

    public static function response()
    {
        return new OrderActionResponse();
    }

}