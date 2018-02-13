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
use service\message\customer\TestReportRequest;
use service\models\common\Customer;
use yii\base\Exception;

class test1 extends Customer
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
        /** @var TestReportRequest $request */
        $request = self::parseRequest($data);
        Tools::log('============test1=============','customer_report_for_audit.log');
        Tools::log('IP:'.$this->getRemoteIp(),'customer_report_for_audit.log');
        Tools::log($request,'customer_report_for_audit.log');
    }

    public static function request(){
        return new TestReportRequest();
    }

    public static function response(){
        return true;
    }

}