<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 11:31
 */

namespace service\resources\driver\v1;


use common\models\driver\Order;
use framework\components\barcode\Converter;
use framework\components\ProxyAbstract;
use framework\components\ToolsAbstract;
use service\message\common\Header;
use service\message\driver\DriverOrder;
use service\message\driver\OrderAction;
use service\message\driver\OrderDetailRequest;
use service\message\sales\DriverOrderDetailRequest;
use service\models\common\CustomerException;
use service\models\common\DriverAbstract;
use service\models\common\DriverException;

class orderDetail extends DriverAbstract
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed
     * @throws CustomerException
     */
    public function run($data)
    {
        /** @var OrderDetailRequest $request */
        $request = self::parseRequest($data);
        $this->initDriver($request);
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
        /** @var Order $driverOrder */
        $driverOrder = Order::findOne($condition);

        if (!$driverOrder) {
            DriverException::orderNotInTheList();
        }

        $header = new Header();
        $header->setRoute('sales.driverOrderDetail');
        $header->setSource($this->getSource());
        $header->setAppVersion($this->getAppVersion());
        $orderDetailRequest = new DriverOrderDetailRequest();
        $orderDetailRequest->setOrderId($driverOrder->order_id);
        $orderDetailRequest->setIncrementId($driverOrder->increment_id);
        $message = ProxyAbstract::sendRequest($header, $orderDetailRequest);
        $order = new \service\message\common\Order();
        $order->parseFromString($message->getPackageBody());
        $date = ToolsAbstract::getDate();
        $response = self::response();
        $data = [
            'driver_order_id' => $driverOrder->entity_id,
            'driver_id' => $driverOrder->driver_id,
            'state' => $driverOrder->state,
            'status' => $driverOrder->status,
            'status_label' => $driverOrder->getStatusLabel(),
            'created_at' => $date->date('Y-m-d H:i:s', $driverOrder->created_at),
            'delivery_time' => $driverOrder->delivery_time,
            'completed_at' => $date->date('Y-m-d H:i:s', $driverOrder->completed_at),
            'lat' => $driverOrder->lat,
            'lng' => $driverOrder->lng,
            'wholesaler_id' => $driverOrder->wholesaler_id,
            'wholesaler_name' => $driverOrder->wholesaler_name,
            'customer_id' => $driverOrder->customer_id,
            'customer_name' => $driverOrder->customer_name,
            'customer_phone' => $driverOrder->customer_phone,
            'increment_id' => $driverOrder->increment_id,
            'order_id' => $driverOrder->order_id,
        ];
        $response->setFrom(ToolsAbstract::pb_array_filter($data));
        $response->setOrder($order);
        return $response;
    }

    public static function request()
    {
        return new OrderDetailRequest();
    }

    public static function response()
    {
        return new DriverOrder();
    }

}