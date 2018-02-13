<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/23
 * Time: 11:59
 */

namespace service\resources\customers\v1;


use common\models\LeCustomers;
use service\components\Tools;
use service\message\customer\CheckCustomerRequest;
use service\message\customer\CheckCustomerResponse;
use service\models\common\Customer;

class checkCustomerIfExist extends Customer
{
    public function run($data){
        /** @var CheckCustomerRequest $request */
        $request = self::parseRequest($data);
        $customer = null;
        if($request->getField() == 1){
            $customer = LeCustomers::checkUserByUserName($request->getUsername());
        }elseif($request->getField() == 2){
            $customer = LeCustomers::checkUserByPhone($request->getPhone());
        }
        Tools::log($customer);
        $response = new CheckCustomerResponse();
        if($customer){
            //用户存在
            $response->setCode(1);
        }else{
            //用户不存在
            $response->setCode(0);
        }
        return $response;
    }

    public static function request()
    {
        return new CheckCustomerRequest();
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }
}