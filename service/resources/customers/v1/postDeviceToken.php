<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/29
 * Time: 15:15
 */

namespace service\resources\customers\v1;


use common\models\DeviceToken;
use service\message\customer\PostDeviceTokenRequest;
use service\models\common\Customer;
use service\models\common\CustomerException;

class postDeviceToken extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param \ProtocolBuffers\Message $data
     * @return mixed|void
     * @throws CustomerException
     */
    public function run($data)
    {
        /** @var PostDeviceTokenRequest $request */
        $request = self::parseRequest($data);

        /* 清除其他相同的token，checksum没有用到，这里就不设置了 */
        if ($request->getToken()) {
            DeviceToken::updateAll(['token' => ''], ['token' => $request->getToken()]);
        }

        $customer = $this->getCustomerModel($request->getCustomerId(), $request->getAuthToken());
        if ($customer) {
            $deviceToken = DeviceToken::findDeviceTokenByCustomerId($customer->getId());
            if (!$deviceToken) {
                $deviceToken = new DeviceToken();
                $deviceToken->created_at = date('Y-m-d H:i:s');
            }
            $deviceToken->customer_id = $request->getCustomerId();
            $deviceToken->token = $request->getToken();
            $deviceToken->system = $request->getSystem();
            $deviceToken->channel = $request->getChannel();
            $deviceToken->checksum = $this->getMd5Sum($request->getToken(), $request->getSystem(), $request->getCustomerId());
            $deviceToken->typequeue = $request->getTypequeue();
            $deviceToken->updated_at = date('Y-m-d H:i:s');
            $deviceToken->save();
        } else {
            CustomerException::customerAuthTokenExpired();
        }
    }

    /**
     * Function: getMd5Sum
     * Author: Jason Y. Wang
     *
     * @param $token
     * @param $system
     * @param $customer_id
     * @return string
     */
    private function getMd5Sum($token, $system, $customer_id)
    {
        return md5(sprintf('%s%s%s', $token, $system, $customer_id));
    }

    public static function request()
    {
        return new PostDeviceTokenRequest();
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }

}