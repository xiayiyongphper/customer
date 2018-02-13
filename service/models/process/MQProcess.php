<?php
namespace service\models\process;

use common\components\push\CustomerEnterpriseJiGuang;
use common\components\push\CustomerJiGuang;
use common\components\push\MerchantJiGuang;
use common\models\DeviceToken;
use common\models\LeMessagePush;
use common\models\LeMessagePushHistory;
use framework\components\es\Console;
use framework\components\ToolsAbstract;
use framework\core\ProcessInterface;
use framework\core\SWServer;
use framework\mq\MQAbstract;
use PhpAmqpLib\Message\AMQPMessage;
use service\components\Tools;
use service\models\common\CustomerException;
use service\models\customer\Observer;

/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 16-6-2
 * Time: 上午11:12
 */

/**
 * Class MQProcess
 * @package service\models\process
 */
class MQProcess implements ProcessInterface
{
    /**
     * @inheritdoc
     */
    public function run(SWServer $SWServer, \swoole_process $process)
    {
        try {
            Tools::getMQ(true)->consume(function ($msg) {
                /** @var  AMQPMessage $msg */
                Console::get()->log($msg->body, null, [__METHOD__]);
                Tools::log($msg->body, 'mq_process.log');//100000,1, 50000,2,111,000,000,#phone#,//1000
                $body = json_decode($msg->body, true);

                $tags = [];
                $key = ToolsAbstract::arrayGetString($body, 'key');
                $data = ToolsAbstract::arrayGetString($body, 'value');
                switch ($key) {
                    case MQAbstract::MSG_MARKETING_CUSTOMER_PUSH:
                        $tags[] = MQAbstract::MSG_PUSH;
                        self::pushMessage($data);
                        break;
                    // 超市被审核通过
                    case MQAbstract::MSG_CUSTOMER_APPROVED:
                        $tags[] = MQAbstract::MSG_CUSTOMER_APPROVED;
                        Observer::customerCreated($data);
                        Observer::autoApproved($data);
                        break;
                    // 超市注册
                    case MQAbstract::MSG_CUSTOMER_CREATE:
                        $tags[] = MQAbstract::MSG_CUSTOMER_CREATE;
                        Observer::customerCreated($data);
                        break;
                    // 供应商同意取消订单
                    case MQAbstract::MSG_ORDER_AGREE_CANCEL:
                        $tags[] = MQAbstract::MSG_ORDER_AGREE_CANCEL;
                        // 退零钱
                        Observer::return_balance($data['order']);
                        // 活动,每月首单标记
                        Observer::act_monthFirstOrder_orderCancel($data['order']);
                        // push
                        Observer::orderAgreeCancel($data['order']);
                        break;
                    // 订单取消
                    case MQAbstract::MSG_ORDER_CANCEL:
                        $tags[] = MQAbstract::MSG_ORDER_CANCEL;
                        Observer::act_monthFirstOrder_orderCancel($data['order']);
                        Observer::return_balance($data['order']);
                        break;
                    // 超市签收，待评价
                    case MQAbstract::MSG_ORDER_PENDING_COMMENT:
                        $tags[] = MQAbstract::MSG_ORDER_PENDING_COMMENT;
                        Observer::rebates_add($data['order']);
                        Observer::additional_package_to_balance($data['order']);
                        break;
                    // 超市确认收货
                    case MQAbstract::MSG_ORDER_CONFIRM:
                        $tags[] = MQAbstract::MSG_ORDER_CONFIRM;
                        Observer::orderConfirm($data['order']);
                        break;
                    // 供应商拒绝取消订单
                    case MQAbstract::MSG_ORDER_REJECT_CANCEL:
                        $tags[] = MQAbstract::MSG_ORDER_REJECT_CANCEL;
                        $order = $data['order'];
                        Observer::orderRejectCancel($order);
                        break;
                    // 超市拒收
                    case MQAbstract::MSG_ORDER_REJECTED_CLOSED:
                        $tags[] = MQAbstract::MSG_ORDER_REJECTED_CLOSED;
                        $order = $data['order'];
                        Observer::return_balance($order);
                        break;
                    case MQAbstract::MSG_ORDER_NEW:
                        $tags[] = MQAbstract::MSG_ORDER_NEW;
                        $order = $data['order'];
                        Observer::first_order_at($order);
                        Observer::act_monthFirstOrder_orderNew($order);
                        //改为同步消费零钱
                        //Observer::balance_consume($order);
                        Observer::updateLastPlaceOrderAt($order);
                        break;
                    // 手动返现&额度包转钱包
                    case MQAbstract::MSG_ORDER_MANUAL_REBATE:
                        $tags[] = MQAbstract::MSG_ORDER_MANUAL_REBATE;
                        $order = $data['order'];
                        Observer::rebates_add($order);
                        Observer::additional_package_to_balance($order);
                        break;
                    // 手动退零钱
                    case MQAbstract::MSG_ORDER_MANUAL_RETURN_CHANGE:
                        $tags[] = MQAbstract::MSG_ORDER_MANUAL_RETURN_CHANGE;
                        $order = $data['order'];
                        Observer::return_balance($order);
                        break;
                    // 订单关闭
                    case MQAbstract::MSG_ORDER_CLOSED:
                        $tags[] = MQAbstract::MSG_ORDER_CLOSED;
                        $order = $data['order'];
                        Observer::return_balance($order);
                        Observer::orderDecline($order);
                        break;
                    // 秒杀推送
                    case MQAbstract::MSG_MERCHANT_SECKILL_PUSH:
                        $tags[] = MQAbstract::MSG_MERCHANT_SECKILL_PUSH;
                        Observer::seckill_push($data);
                        break;
                    default:
                        $tags[] = MQAbstract::MSG_INVALID_KEY;
                }
                Console::get()->log($msg, null, $tags);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            });
        } catch (\Exception $e) {
            Tools::logException($e);
        }
    }

    /**
     * Author Jason Y. wang
     * @param $data
    (
     * [title] => bb
     * [message] => bbb
     * [customer_id] => |1234|123|1234|123||1234|123||1234|123|1234|123|
     * [created_at] => 2017-02-13 06:55:18
     * [entity_id] => 22
     * )
     */
    protected static function pushMessage($data)
    {

        if (!isset($data['entity_id']) || !isset($data['title']) ||
            !isset($data['message']) || !isset($data['customer_id'])
        ) {
            $push_model = LeMessagePush::findOne(['entity_id']);
            $push_model->status = LeMessagePush::LE_MESSAGE_PUSH_STATUS_FAIL;
            $push_model->save();
            return;
        }

        $push_id = $data['entity_id'];

        $push_model = LeMessagePush::findOne(['entity_id' => $push_id]);
        if ($push_model) {
            $push_model->status = LeMessagePush::LE_MESSAGE_PUSH_STATUS_PROCESSING;
            $push_model->save();
        } else {
            $push_model->status = LeMessagePush::LE_MESSAGE_PUSH_STATUS_FAIL;
            $push_model->save();
            return;
        }

        if ($data['customer_id']) {
            $scheme = isset($data['scheme']) ? $data['scheme'] : '';
            $params = array(
                'title' => $data['title'],
                'content' => $data['message'],
            );
            Tools::log($scheme, 'wangyang.txt');
            // 默认不传scheme会打开app
            if ($scheme) {
                $params['scheme'] = $scheme;
            } else {
                $params['scheme'] = 'lelaishop://';
            }

            $customer_ids = array_filter(explode('|', $data['customer_id']));
            //一次查出所有需要推送的Token
            $queues = DeviceToken::find()->where(['customer_id' => $customer_ids])
                ->orderBy('entity_id desc')->groupBy('customer_id')->asArray()->all();
            $result = [];
            //按用户推送，如果没有对应token则记录为失败
            foreach ($customer_ids as $customer_id) {
                $queueArray = null;
                //查找对应Token
                foreach ($queues as $key => $queue) {
                    if ($queue['customer_id'] == $customer_id) {
                        $queueArray = $queue;
                        break;
                    }
                }
                //没有对应token则记录为失败
                if ($queueArray) {
                    $message = array(
                        'system' => $queueArray['system'],
                        'token' => $queueArray['token'],
                        'platform' => 1,
                        'channel' => $queueArray['channel'],
                        'value_id' => $customer_id,
                        'typequeue' => $queueArray['typequeue'],
                        'params' => $params,
                    );
                    //推送
                    self::send($push_id, $customer_id, $message, $result);
                } else {
                    //推送异常
                    $push_result = [];
                    array_push($push_result, null);
                    array_push($push_result, $push_id);
                    array_push($push_result, $customer_id);
                    array_push($push_result, 'failed');
                    array_push($push_result, LeMessagePushHistory::LE_MESSAGE_PUSH_DEVICE_TOKEN_ERROR);
                    array_push($push_result, date('Y-m-d H:i:s'));
                    array_push($result, $push_result);
                }
            }
            if ($result) {
                \Yii::$app->mainDb->createCommand()->batchInsert(LeMessagePushHistory::tableName(),
                    LeMessagePushHistory::getTableSchema()->getColumnNames(), $result)->execute();
            }
            $push_model->status = LeMessagePush::LE_MESSAGE_PUSH_STATUS_COMPLETED;
            $push_model->save();
        }
    }

    /**
     * Function: send
     * Author: Jason Y. Wang
     * 推送消息
     * @param $push_id
     * @param $customer_id
     * @param $message
     * @param $result
     * @return bool
     */
    public static function send($push_id, $customer_id, $message, &$result)
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
            $params = $message['params'];
            if (!$params) {
                CustomerException::customerSystemError();
            }

            //推送参数
            $data = [
                'user_id' => $message['token'],
                'title' => $params['title'],
                'content' => $params['content'],
                'scheme' => $params['scheme'],
                'mobilesys' => $mobilesys,
                'sendno' => rand(1, 100000)
            ];
            //推送
            $queue->push($data);
        } catch (\Exception $e) {
            //推送异常
            $push_result = [];
            array_push($push_result, null);
            array_push($push_result, $push_id);
            array_push($push_result, $customer_id);
            array_push($push_result, 'failed');
            array_push($push_result, $e->getMessage());
            array_push($push_result, date('Y-m-d H:i:s'));
            array_push($result, $push_result);
        }
    }
}
