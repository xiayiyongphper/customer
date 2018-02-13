<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 11:31
 */

namespace service\resources\driver\v1;

use common\components\DriverSms;
use service\message\customer\GetSmsRequest;
use service\models\common\DriverAbstract;
use service\models\common\DriverException;

class sms extends DriverAbstract
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed
     */
    public function run($data)
    {
        /** @var GetSmsRequest $request */
        $request = self::parseRequest($data);

        if($request->getToken() != DriverSms::SMS_TOKEN){
            DriverException::driverAuthTokenExpired();
        }

        if($result = DriverSms::sendMessage($request)){
            return true;
        }

        DriverException::driverCodeSendError();

    }

    public static function request(){
        return new GetSmsRequest();
    }

    public static function response(){
        return true;
    }
}