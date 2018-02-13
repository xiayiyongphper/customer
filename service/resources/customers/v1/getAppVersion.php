<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/2
 * Time: 16:30
 */

namespace service\resources\customers\v1;

use common\models\LeAppVersion;
use service\components\Tools;
use service\message\customer\GetAppVersionRequest;
use service\message\customer\GetAppVersionResponse;
use service\models\common\Customer;

class getAppVersion extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     * 获取更新接口
     * @param \ProtocolBuffers\Message $data
     * @return mixed|void
     */
    public function run($data)
    {
        /** @var GetAppVersionRequest $request */
        $request = self::parseRequest($data);
        //渠道号  1XXXXX：IOS正式版       2XXXXX：IOS企业版        3XXXXX：android
        //后面渠道号可以保存为数组，这样可以分渠道进行升级
        if ($request->getChannel() >= 100000 && $request->getChannel() < 200000) {
            $channel = 100000;
        } elseif ($request->getChannel() >= 200000 && $request->getChannel() < 300000) {
            $channel = 200000;
        } else {
            $channel = 300000;
        }

        $customer = $this->getCustomerModel($request->getCustomerId(), $request->getAuthToken());
        if ($customer && $customer->app_version_id) {
            /* @var  $appVersion LeAppVersion */
            $appVersion = LeAppVersion::find()->where([
                    'entity_id' => $customer->app_version_id,
                    'platform' => $request->getPlatform(),
                    'channel' => $channel
                ])->one();
        } else {
            /* @var  $appVersion LeAppVersion */
            $appVersion = LeAppVersion::find()->where([
                'system' => $request->getSystem(),
                'platform' => $request->getPlatform(),
                'channel' => $channel,
                'is_debug'=>0
            ])->orderBy(['entity_id' => SORT_DESC])->one();
        }

        $response = self::response();
        if ($appVersion && $appVersion->entity_id && (version_compare($appVersion->version, $request->getVersion(), '>') || $customer->app_version_id)) {
            $responseArray = [
                'title' => $appVersion->title,
                'url' => $appVersion->url,
                'version' => $appVersion->version,
                'description' => $appVersion->description,
                'type' => version_compare($request->getVersion(), $appVersion->lowest_version, '<') ? 1 : $appVersion->type, //比较用户当前版本和最低要求更新版本来确定是否需要强制更新
                'is_recommend' => $appVersion->is_recommend,
                'recommend_interval' => $appVersion->recommend_interval * 60,
                'recommend_max_time' => $appVersion->recommend_max_time,
            ];
            $response->setFrom($responseArray);
        }
        return $response;
    }

    public static function request()
    {
        return new GetAppVersionRequest();
    }

    public static function response()
    {
        return new GetAppVersionResponse();
    }
}