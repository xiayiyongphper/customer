<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 11:31
 */

namespace service\resources\driver\v1;

use common\models\driver\Order;
use service\models\common\DriverAbstract;
use service\models\common\DriverException;

class deleteOrder extends DriverAbstract
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed
     */
    public function run($data)
    {
        /** @var \service\message\driver\DeleteOrder $request */
        $request = self::parseRequest($data);
        $driver = $this->initDriver($request);

        $driver_order_id = $request->getDriverOrderId();

        if(!$driver_order_id){
            DriverException::driverOrderIdInvalid();
        }

        /** @var Order $driverOrder */
        $driverOrder = Order::getByDriverOrderId($driver_order_id);

        if(!$driverOrder){
            DriverException::orderNotExist();
        }
        $driverOrder->setDriver($driver);
        $driverOrder->setState(Order::STATE_NEW,Order::STATUS_PENDING,'置为删除状态',null);

        if($driverOrder->delete()){
            return true;
        }else{
            DriverException::driverSystemError();
        }
    }

    public static function request(){
        return new \service\message\driver\DeleteOrder();
    }

    public static function response(){
        return true;
    }
}