<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 11:31
 */

namespace service\resources\customers\v1;

use service\message\common\UniversalResponse;
use service\message\customer\GetSmsRequest;
use common\components\CustomerSms;
use service\models\common\Customer;
use service\models\common\CustomerException;

class sms extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed
     * @throws CustomerException
     */
    public function run($data)
    {
        /** @var GetSmsRequest $request */
        $request = self::parseRequest($data);
        if ($request->getToken() == CustomerSms::SMS_TOKEN) {
            $code = CustomerSms::sendMessage($request);
            $response = new UniversalResponse();
            $response->setCode(0);
            $response->setMessage($code);
            return $response;
        } else {
            CustomerException::customerAuthTokenExpired();
        }
    }

    public static function request()
    {
        return new GetSmsRequest();
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }

}