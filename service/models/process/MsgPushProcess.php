<?php
namespace service\models\process;

use common\components\push\CustomerEnterpriseJiGuang;
use common\components\push\CustomerJiGuang;
use common\components\push\MerchantJiGuang;
use framework\components\es\Console;
use framework\components\ToolsAbstract;
use framework\core\ProcessInterface;
use framework\core\SWServer;
use service\components\Proxy;
use service\components\Tools;
use service\models\common\CustomerException;
use service\resources\Exception;

/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 16-6-2
 * Time: 上午11:12
 */

/**
 * Class MsgPushProcess 推送消息
 * @package service\models
 */
class MsgPushProcess implements ProcessInterface
{
    const MESSAGE_PUSH_QUEUE = 'message_push_queue';

    /**
     * @inheritdoc
     */
    public function run(SWServer $SWServer, \swoole_process $process)
    {
        $redis = Tools::getRedis();
        while (true) {
            try {
                if ($redis->lLen(self::MESSAGE_PUSH_QUEUE) == 0) {
                    sleep(1);
                    continue;
                }
                /** @var  array $queue */
                $message = $redis->lPop(self::MESSAGE_PUSH_QUEUE);
                $message = unserialize($message);
                if (is_array($message)) {
                    $result = self::send($message);
                    if (!$result) {
                        //不成功时需要记录下来
                    }
                }
            } catch (\Exception $e) {
                Tools::logException($e);
            }
        }
    }

    /**
     * Function: send
     * Author: Jason Y. Wang
     * 推送消息
     * @param $message
     * @return bool
     */
    public static function send($message)
    {
        $mobilesys = 'android';
        $queue = new CustomerJiGuang();
        switch ($message['platform']) {
            case 1:
                if ($message['channel'] >= 100000 && $message['channel'] < 200000) {
                    $mobilesys = 'ios';
                    $queue = new CustomerJiGuang();
                } elseif ($message['channel'] >= 200000 && $message['channel'] < 300000) {
                    $mobilesys = 'ios';
                    $queue = new CustomerEnterpriseJiGuang();
                } else {
                    $mobilesys = 'android';
                    $queue = new CustomerJiGuang();
                }
                break;
            case 2:
                if ($message['channel'] >= 100000 && $message['channel'] < 300000) {
                    $mobilesys = 'ios';
                    $queue = new MerchantJiGuang();
                } else {
                    $mobilesys = 'android';
                    $queue = new MerchantJiGuang();
                }
                break;
            default:
                break;
        }

        try {

            $params = unserialize($message['params']);
            if (!$params) {
                CustomerException::customerSystemError();
            }

            //推送参数
            $data = array('user_id' => $message['token'], 'title' => $params['title'], 'content' => $params['content'], 'scheme' => $params['scheme'], 'mobilesys' => $mobilesys, 'sendno' => rand(1, 100000));
            //推送
            $result = $queue->push($data);
            Tools::log('#############', 'wangyang.txt');
            Tools::log($result, 'wangyang.txt');
            Tools::log('#############', 'wangyang.txt');
            return $result;
        } catch (\Exception $e) {
            Tools::logException($e);
        }
    }
}