<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 17:22
 */

namespace service\resources\customers\v1;


use common\components\CustomerSms;
use common\models\LeCustomers;
use common\models\VerifyCode;
use service\components\Tools;
use service\message\common\UniversalResponse;
use service\message\customer\ConfirmVerifyCodeRequest;
use service\message\customer\ResetPasswordRequest;
use service\models\common\Customer;
use service\models\common\CustomerException;

class confirmVerifyCode extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     * 确认验证码
     * @param $data
     * @return mixed|UniversalResponse
     * @throws CustomerException
     */
    public function run($data)
    {
        /** @var ConfirmVerifyCodeRequest $request */
        $request = self::parseRequest($data);
        $response = new UniversalResponse();
        $verify = VerifyCode::find()
            ->where(['phone' => $request->getPhone(), 'verify_type' => $request->getType()])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
        //判断验证码是否正确
        if($verify && $verify['code'] == $request->getCode()){
            //过期时间60秒
            if (strtotime($verify['created_at']) + self::CODE_EXPIRATION_TIME > time()) {
                $response->setCode(0);
                $response->setMessage('验证码有效');
                return $response;
            } else {
                CustomerException::verifyCodeExpired();
            }
        }else{
            CustomerException::verifyCodeError();
        }
    }

    public static function request()
    {
        return new ConfirmVerifyCodeRequest();
    }

    public static function response()
    {
        return new UniversalResponse();
    }
}