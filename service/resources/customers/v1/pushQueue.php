<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/29
 * Time: 15:15
 */

namespace service\resources\customers\v1;


use common\models\LeAppPushQueue;
use service\message\customer\PushQueueRequest;
use service\models\common\Customer;

class pushQueue extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     * 将信息加入推送队列
     * @param \ProtocolBuffers\Message $data
     * @return mixed|void
     */
    public function run($data){
        /** @var PushQueueRequest $request */
        $request = self::parseRequest($data);
        $pushQueue = new LeAppPushQueue();
        $pushQueue->token = $request->getToken();
        $pushQueue->channel = $request->getChannel();
        $pushQueue->platform = $request->getPlatForm();
        $pushQueue->status = $request->getStatus();
        $pushQueue->group_id = $request->getGroupId();
        $pushQueue->value_id = $request->getValueId();
        $pushQueue->message = $request->getMessage();
        $pushQueue->params = $request->getParams();
        $pushQueue->priority = $request->getPriority();
        $pushQueue->checksum = $request->getChecksum();
        $pushQueue->typequeue = $request->getTypequeue();
        $pushQueue->created_at = date('Y-m-d H:i:s',time());
        $pushQueue->save();
    }

    public static function request()
    {
        return new PushQueueRequest();
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }

}