<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 14:17
 */

namespace service\models\common;

use common\models\driver\Driver;
use common\models\driver\Order;
use framework\protocolbuffers\Message;
use service\components\Tools;
use service\message\driver\DriverInfo;
use service\resources\ResourceAbstract;

abstract class DriverAbstract extends ResourceAbstract
{
    //验证码过期时间
    const CODE_EXPIRATION_TIME = 60; //60s

    public static function parseRequest($data){
        /** @var Message $request */
        $request = get_called_class()::request();
        $request->parseFromString($data);
        return $request;
    }

    /**
     * getDriverInfo
     * Author Jason Y. wang
     * 司机信息
     * @param $phone
     * @return bool|DriverInfo
     */
    public function getDriverInfo($phone)
    {
        if(!$phone){
            DriverException::driverNotExist();
        }
        /** @var Driver $driver */
        $driver = Driver::findByPhone($phone);

        if(!$driver){
            DriverException::driverNotExist();
        }

        $response = new DriverInfo();
        $responseData = array(
            'driver_id' => $driver->entity_id,
            'name' => $driver->name,
            'phone' => $driver->phone,
            'auth_token' => $driver->auth_token,
            'wholesaler_id' => $driver->wholesaler_id,
            'city' => $driver->city,
            'sex' => $driver->gender,
            'wholesaler_name' => $driver->wholesaler_name,
            'city_name' => $driver->city_name
        );
        $response->setFrom(Tools::pb_array_filter($responseData));
        return $response;


    }

    /**
     * @param $data
     * @return Driver
     * @throws ContractorException
     */
    protected function initDriver($data)
    {
        if ($data->getDriverId() && $data->getAuthToken()) {
            $driver = Driver::find()->where(['entity_id' => $data->getDriverId(), 'auth_token' => $data->getAuthToken()])->one();
            if ($driver) {
                return $driver;
            }
        }
        DriverException::driverAuthTokenExpired();
    }

    /**
     * @param Order $driverOrder
     * @return array
     */
    protected function convertDriverArray($driverOrder){
        $driverOrder = [
            'driver_order_id' => $driverOrder->entity_id,
            'driver_id' => $driverOrder->driver_id,
            'state' => $driverOrder->state,
            'status' => $driverOrder->status,
            'created_at' => $driverOrder->created_at,
            'lat' => $driverOrder->lat,
            'lng' => $driverOrder->lng,
            'wholesaler_id'=>$driverOrder->wholesaler_id,
            'wholesaler_name'=>$driverOrder->wholesaler_name,
            'customer_id'=>$driverOrder->customer_id,
            'customer_name'=>$driverOrder->customer_name,
            'customer_phone'=>$driverOrder->customer_phone,
            'increment_id'=>$driverOrder->increment_id,
            'order_id'=>$driverOrder->order_id,
            'delivery_time' => $driverOrder->delivery_time,
        ];
        return $driverOrder;
    }

}