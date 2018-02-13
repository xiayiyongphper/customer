<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 18:31
 */
namespace service\resources\driver\v1;

use service\components\Tools;
use service\message\driver\LoginRequest;
use service\models\common\DriverAbstract;

class loginTest extends DriverAbstract
{

    public function run($data)
    {
        /** @var LoginRequest $request */
        $request = self::parseRequest($data);
        Tools::log('============loginTest=============','driver_report_for_audit.log');
        Tools::log('IP:'.$this->getRemoteIp(),'driver_report_for_audit.log');
        Tools::log($request,'driver_report_for_audit.log');

    }

    public static function request(){
        return new LoginRequest();
    }

    public static function response(){
        return true;
    }

}