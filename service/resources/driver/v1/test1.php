<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 18:31
 */
namespace service\resources\driver\v1;

use service\components\Tools;
use service\message\customer\CustomerResponse;
use service\message\customer\TestReportRequest;
use service\models\common\DriverAbstract;
use yii\base\Exception;

class test1 extends DriverAbstract
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
        Tools::log('============test1=============','driver_report_for_audit.log');
        Tools::log('IP:'.$this->getRemoteIp(),'driver_report_for_audit.log');
        Tools::log($request,'driver_report_for_audit.log');
    }

    public static function request(){
        return new TestReportRequest();
    }

    public static function response(){
        return true;
    }

}