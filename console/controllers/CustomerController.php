<?php

namespace console\controllers;

use common\components\push\lib\JiGuangPush;
use common\components\UserTools;
use common\models\LeCustomers;
use common\models\RegionArea;
use service\components\Tools;
use service\models\customer\Observer;
use yii\console\Controller;
use yii\db\Expression;

/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/1
 * Time: 14:40
 */
class CustomerController extends Controller
{
    /**
     * Function: actionTest
     * Author: Jason Y. Wang
     *
     */
    public function actionTest()
    {
        $lat = '22.541284';
        $lng = '113.960701';
        $distance = new Expression('(ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $lat . '*PI()/180-`lat`*PI()/180)/2),2)+COS(' . $lat . '*PI()/180)*COS(`lat`*PI()/180)*POW(SIN((' . $lng . '*PI()/180-`lng`*PI()/180)/2),2)))*1000)) as distance');
        $stores = LeCustomers::find()->select(['*', $distance])->orderBy('distance asc')->limit(10)->all();
        foreach ($stores as $store) {
            print_r($store);
            //exit();
        }
//        $sql = $stores->createCommand()->getRawSql();
//        print_r($sql);
//        $stores = LeCustomers::getDb()->createCommand($sql)->queryAll();
//        print_r($stores);
    }

    /**
     * Function: actionPush
     * Author: Jason Y. Wang
     * 队列推送脚本
     */
    public function actionPush()
    {
        $n_title = '乐来';
        $n_content = '11111111';
        $sendno = time();
        $receiver_value = '160a3797c804851dfc8';
        $mobilesys = 'android';
        $scheme = 'lelaiwholesaler://order/info?oid=160&sound=new';
        $obj = new JiGuangPush('de959cea06824cc947ac53e5', 'de58d8aeb9195fabdc4eb179');
        echo 111111;
        if ($mobilesys == 'ios') {
            $msg_content = json_encode(array('n_title' => $n_title, 'n_content' => $n_content, 'n_extras' => array('scheme' => $scheme)));
            $res = $obj->send($sendno, 5, $receiver_value, 1, $msg_content, $mobilesys);
        } else {
            $msg_content = json_encode(array('message' => json_encode(array('title' => $n_title, 'scheme' => urlencode($scheme), 'content' => $n_content))));
            echo 123;
            print_r(json_decode($msg_content, true));
            $res = $obj->send($sendno, 5, $receiver_value, 2, $msg_content, $mobilesys);
        }
        Tools::log('#######length################', 'wangyang.txt');
        Tools::log(strlen($msg_content), 'wangyang.txt');
        if (0 !== $res['errcode']) {
            //throw new \Exception(sprintf('ERROR NUMBER: %s,ERROR MESSAGE: %s', $res['errcode'],$res['errmsg']));
            print_r(sprintf('ERROR NUMBER: %s,ERROR MESSAGE: %s', $res['errcode'], $res['errmsg']));
        }
        exit();
        //      return true;
    }

    public function actionRelocatecustomer()
    {
        $customers = LeCustomers::find()->all();
        /** @var LeCustomers $customer */
        foreach ($customers as $customer) {
            $area_id = UserTools::findAreaIdByLocation($customer->lat, $customer->lng);
            $customer->area_id = $area_id;
            if ($area_id && $customer->save()) {
                echo $customer->username . '重新分配完成';
                echo PHP_EOL;
            } else {
                echo $customer->username . '重新分配失败';
                echo PHP_EOL;
            }
        }
        $a = LeCustomers::find()->where(['status' => 1])->andWhere(['area_id' => -1])->count();
        $b = LeCustomers::find()->where(['status' => 1])->count();
        $c = LeCustomers::find()->count();
        echo '所有超市数量:' . $c . PHP_EOL;
        echo '正在营业但未分配成功的数量:' . $a . PHP_EOL;
        echo '正在营业的数量' . $b . PHP_EOL;
    }

    public function actionExportRelocatedCustomer()
    {
        $customers = LeCustomers::find()
            ->andWhere(['city' => 442000])
            ->all();
        echo '用户ID,区域' . PHP_EOL;
        /** @var LeCustomers $customer */
        foreach ($customers as $customer) {
            $area_id = $this->findAreaIdByLocation($customer->lat, $customer->lng);
            echo $customer->entity_id . ',' . $area_id . PHP_EOL;
        }
    }

    /**
     * 通过超市经纬找出这个超市所在的area ID
     * @param $lng
     * @param $lat
     * @return array|bool
     */
    public function findAreaIdByLocation($lat, $lng)
    {
        $areaId = -1;
        if ($lat && $lng) {
            $areas = (new RegionArea())->find();
            //根据最大最小经纬度粗略查找所在区域  374  375
            $areas->where(['<', 'min_lat', $lat])->andWhere(['>', 'max_lat', $lat])
                ->andWhere(['<', 'min_lng', $lng])->andWhere(['>', 'max_lng', $lng])
                ->andWhere(['entity_id' => [374, 375]]);
            $areasOne = $areas->asArray()->all();
            foreach ($areasOne as $key => $item) {
                //精细查找
                if (UserTools::whetherInTheRange($lat, $lng, $item)) {
                    //运营保证区域ID不重合，一个超市只能属于一个区域ID
                    //发现匹配的区域后，直接退出循环
                    //如果后面也有匹配区域暂时不做处理
                    $areaId = $item['entity_id'];
                    break;
                }
            }
        }
        return $areaId;
    }

    public function actionPushOneMessage()
    {

    }

    public function actionPushBalanceMessage()
    {
        Observer::pushBalanceMessage();
    }

    public function actionIndex()
    {

    }

    public function resetPassword($start, $end)
    {
        $passwords = [];
        $customers = LeCustomers::find()->where(['between', 'entity_id', $start, $end])->all();
        if (count($customers) > 0) {
            /** @var LeCustomers $customer */
            foreach ($customers as $customer) {
                if (isset($passwords[$customer->password]) && $passwords[$customer->password]) {
                    $customer->new_password = $passwords[$customer->password];
                } else {
                    $password = password_hash($customer->password, PASSWORD_DEFAULT, ['cost' => 14]);
                    $passwords[$customer->password] = $password;
                    $customer->new_password = $password;
                }

                if ($customer->save()) {
                    //echo $customer->username . '密码生成完成';
                    //echo PHP_EOL;
                } else {
                    echo $customer->username . '密码生成失败';
                    echo PHP_EOL;
                }
            }
        }
    }


    /**
     * Author Jason Y. wang
     * 222
     */
    public function actionPcntl()
    {
        $startTime = microtime(true);
        $step = 1600;
        $workers = 10;
        $pids = array();
        for ($i = 0; $i < $workers; $i++) {
            $pids[$i] = pcntl_fork();
            switch ($pids[$i]) {
                case -1:
                    echo "fork error : {$i} \r\n";
                    exit;
                case 0:
                    $start = $i * $step;
                    $end = ($i + 1) * $step;
                    $this->resetPassword($start, $end);
                    exit;
                default:
                    break;
            }
        }

        foreach ($pids as $i => $pid) {
            if ($pid) {
                pcntl_waitpid($pid, $status);
            }
        }
        $endTime = microtime(true);
        echo $startTime;
        echo $endTime;
    }

    public function actionResetPassword()
    {
        $customers = LeCustomers::find()->where(['>', 'entity_id', 16000])->all();
//        print_r(count($customers));
//        exit();
        /** @var LeCustomers $customer */
        foreach ($customers as $customer) {

            $password = password_hash($customer->password_old, PASSWORD_DEFAULT, ['cost' => 14]);
            $customer->password = $password;

            if ($customer->save()) {
                echo $customer->username . '密码生成完成';
                echo PHP_EOL;
            } else {
                echo $customer->username . '密码生成失败';
                echo PHP_EOL;
            }
        }
    }

    /**
     * 将用户的账号余额重置为0
     * @date 2017-03-15 18:38:05
     * @author henryzhu
     */
    public function actionResetBalance()
    {
        $customerIds = [
            13889, 22667
        ];
        $customerIds = [
            13907, 15364, 6271, 11034, 13674, 11150, 11117, 11154,
            10238, 17256, 13863, 12484, 6328, 8733, 8679, 15358, 11330, 13514,
            13525, 9373, 10688, 11348, 6664, 8946, 4015, 14330, 10662, 12804,
            11036, 6708, 4965, 12376, 8924, 9441, 6672, 15362, 4941, 15357, 6087,
            13672, 11032, 5456, 10383, 6666, 11096, 8841, 11152, 5134, 10691,
            11145, 10232, 6269
        ];
        foreach ($customerIds as $customerId) {
            /** @var LeCustomers $customer */
            $customer = LeCustomers::findByCustomerId($customerId);
            $customer->resetBalance('余额清除，进行线下结算');
        }
    }
}
