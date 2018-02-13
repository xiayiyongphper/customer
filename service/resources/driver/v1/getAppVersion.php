<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/2
 * Time: 16:30
 */

namespace service\resources\driver\v1;

use common\models\LeAppVersion;
use service\message\customer\GetAppVersionRequest;
use service\message\customer\GetAppVersionResponse;
use service\models\common\DriverAbstract;


class getAppVersion extends DriverAbstract
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     * 司机版获取更新接口
     * @param \ProtocolBuffers\Message $data
     * @return mixed|void
     */
    public function run($data){
        /** @var GetAppVersionRequest $request */
        $request = self::parseRequest($data);
        //渠道号  1XXXXX：IOS正式版       2XXXXX：IOS企业版        3XXXXX：android
        //后面渠道号可以保存为数组，这样可以分渠道进行升级
        if($request->getChannel() >= 100000 && $request->getChannel() < 200000){
            $channel = 100000;
        }elseif($request->getChannel() >= 200000 && $request->getChannel() < 300000){
            $channel = 200000;
        }else{
            $channel = 300000;
        }
        /* @var  $appVersion LeAppVersion */
        $appVersion = LeAppVersion::find()
            ->where(['system' => $request->getSystem(),'platform' => $request->getPlatform()])
            ->andWhere(['channel' => $channel])
            ->orderBy(['entity_id' => SORT_DESC])
            ->one();

        $response = new GetAppVersionResponse();
        if ($appVersion && $appVersion->entity_id && version_compare($appVersion->version, $request->getVersion(), '>')) {
            $responseArray = array(
                'title' => $appVersion->title,
                'url' => $appVersion->url,
                'version' => $appVersion->version,
                'description' => $appVersion->description,
                'type' => version_compare($request->getVersion(), $appVersion->lowest_version, '<') ? 1 : $appVersion->type //比较用户当前版本和最低要求更新版本来确定是否需要强制更新
            );
            $response->setFrom($responseArray);
            return $response;
        }
        return true;
    }

    public static function request(){
        return new GetAppVersionRequest();
    }

    public static function response(){
        return new GetAppVersionResponse();
    }
}