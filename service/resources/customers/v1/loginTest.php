<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 18:31
 */
namespace service\resources\customers\v1;

use service\components\Tools;
use service\message\customer\CustomerResponse;
use service\message\customer\LoginRequest;
use service\models\common\Customer;
use yii\base\Exception;

class loginTest extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return CustomerResponse
     * @throws Exception
     */
    public function run($data)
    {
        /** @var LoginRequest $request */
        $request = self::parseRequest($data);
        Tools::log('============loginTest=============','customer_report_for_audit.log');
        Tools::log('IP:'.$this->getRemoteIp(),'customer_report_for_audit.log');
        Tools::log($request,'customer_report_for_audit.log');
    }

    public static function request(){
        return new LoginRequest();
    }

    public static function response(){
        return new CustomerResponse();
    }

}