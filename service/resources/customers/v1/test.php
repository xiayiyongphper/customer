<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 18:31
 */
namespace service\resources\customers\v1;

use common\models\LeCustomers;
use common\models\VerifyCode;
use service\components\Tools;
use service\message\customer\TestReportRequest;
use service\models\common\Customer;

class test extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed|void
     */
    public function run($data)
    {
        /** @var TestReportRequest $request */
        $request = self::parseRequest($data);
        Tools::log('============test=============','customers_report_for_audit.log');
        Tools::log('IP:'.$this->getRemoteIp(),'customers_report_for_audit.log');
        Tools::log($request,'customers_report_for_audit.log');

        $customer = LeCustomers::find()->where(['entity_id'=>1021])->one();

//        try{
//            $verifyCode = new VerifyCode();
//            $verifyCode->phone = '18682002348';
//            $verifyCode->code = 123;
//            $verifyCode->save();
//            Tools::log($verifyCode->errors,'schema.log');
//
//        }catch (\Exception $e){
//            Tools::log($e->getMessage(),'schema.log');
//        }catch (\Error $e){
//            Tools::log($e->getMessage(),'schema.log');
//        }

    }

    public static function request(){
        return new TestReportRequest();
    }

    public static function response(){
        return true;
    }

}