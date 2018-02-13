<?php

namespace console\controllers;

use common\models\LeCustomers;
use common\models\VerifyCode;
use framework\components\barcode\Converter;
use framework\components\mq\Customer;
use yii\console\Controller;

/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/1
 * Time: 14:40
 */
class IndexController extends Controller
{

    public function actionIndex()
    {
        $customer = LeCustomers::findByCustomerId(35);
        Customer::publishApprovedEvent($customer->toArray());
    }

    public function actionOrderIdDecode()
    {
        $order_id = Converter::get()->decode('738615824734');
        echo $order_id;
    }

    public function actionReadWriteSeparation()
    {
//        $customer = LeCustomers::find()->where(['entity_id'=>1021])->one();
//        $customer = LeCustomers::find()->where(['entity_id'=>1021])->asArray()->all();

//        $verifyCode = new VerifyCode();
//        $verifyCode->phone = 90000000;
//        $verifyCode->code = 123;
//        $result1 = $verifyCode->save();
//        echo $verifyCode->isNewRecord;
//        $result2 = $verifyCode->save();
//        echo $verifyCode->isNewRecord;
//        print_r($result1);
//        print_r($result2);

        for ($i = 0; $i < 1; $i++) {
            /** @var LeCustomers $customer */
            $customer = LeCustomers::find()->where(['entity_id' => 1021])->one();
            $customer->created_at = '2017';
            $customer->save();
        }
    }

}
