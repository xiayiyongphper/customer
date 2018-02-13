<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 17:22
 */

namespace service\resources\customers\v1;


use common\components\CustomerSms;
use common\components\UserTools;
use common\models\LeCustomers;
use common\models\LoginDeviceToken;
use common\models\VerifyCode;
use framework\components\Security;
use service\components\Tools;
use service\message\common\SourceEnum;
use service\message\customer\CustomerResponse;
use service\message\customer\ResetPasswordRequest;
use service\models\common\Customer;
use service\models\common\CustomerException;

class resetPassword extends Customer
{
    /**
     * 最大登录次数
     */
    const DEFAULT_MAX_LOGIN_ACOUNT_COUNT_PER_DEVICE = 3;
    /**
     * Function: run
     * Author: Jason Y. Wang
     * 修改密码
     * @param $data
     * @return bool
     * @throws CustomerException
     */
    public function run($data)
    {
        /** @var ResetPasswordRequest $request */
        $request = self::parseRequest($data);
        switch ($request->getType()) {
            case 1: //忘记密码
                $response = $this->forgetPassword($request);
                break;
            case 2:  //修改密码
                $response = $this->modifyPassword($request);
                break;
            default:
                CustomerException::customerSystemError();
                break;
        }
        return $response;
    }

    /**
     * Function: modifyPassword
     * Author: Jason Y. Wang
     * 修改密码，需要是登陆状态
     * @param ResetPasswordRequest $request
     * @return \service\message\customer\CustomerResponse
     * @throws CustomerException
     */
    public function modifyPassword(ResetPasswordRequest $request){
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(), $request->getAuthToken());

		if (!$customer) {
			CustomerException::customerAuthTokenExpired();
		}

		/** @var VerifyCode $verify */
		$verify = VerifyCode::find()
			->where(['phone' => $request->getPhone(), 'verify_type' => CustomerSms::CUSTOMER_SMS_TYPE_FORGET])
			->orderBy(['created_at' => SORT_DESC])
			->one();

		// 读取用户
		/** @var LeCustomers $customer */
		$customer = LeCustomers::findByCustomerId($request->getCustomerId());

		// 收银机机和pcweb验证原密码
		if ($this->getSource() == SourceEnum::ANDROID_CASH || $this->getSource() == SourceEnum::PCWEB) {
			if (!$request->getOriginalPassword() || !LeCustomers::verifyPassword($customer, $request->getOriginalPassword()) ){
				CustomerException::customerOriginalPasswordError();
			}
		}
		// 普通app验证code
		else if (!$verify || $verify['code'] != $request->getCode()) {
			CustomerException::verifyCodeError();
		}

		// 修改密码
		$customer->password = Security::passwordHash($request->getNewPassword());
		$customer->auth_token = UserTools::getRandomString(16);

		if (!$customer->save()) {
			CustomerException::customerSystemError();
		}

		$response = $this->getCustomerInfo($customer);
		return $response;

    }

    /**
     * Function: forgetPassword
     * Author: Jason Y. Wang
     * 忘记密码，不需要登陆
     * @param ResetPasswordRequest $request
     * @return CustomerResponse
     * @throws \Exception|CustomerException
     */
    public function forgetPassword(ResetPasswordRequest $request){
        /** @var VerifyCode $verify */
        $verify = VerifyCode::find()
            ->where(['phone' => $request->getPhone(),'verify_type' => CustomerSms::CUSTOMER_SMS_TYPE_FORGET ])
            ->orderBy(['created_at' => SORT_DESC ])
            ->one();

        // 验证码错误
		if (!$verify || $verify['code'] != $request->getCode()) {
			CustomerException::verifyCodeError();
		}

		// 读取用户
		/** @var LeCustomers $customer */
		$customer = LeCustomers::findByPhone($request->getPhone());

		// 不存在此用户
		if(!$customer){
			CustomerException::customerPhoneNotRegistered();
		}

		// 修改密码
		$customer->password = Security::passwordHash($request->getNewPassword());
		$customer->auth_token = UserTools::getRandomString(16);
		if(!$customer->save()){
			CustomerException::customerSystemError();
		}

		/* 设备登录次数 */
		if (!empty($this->getDeviceId())) {
			$check_pass = $customer->checkLoginDevice($this->getDeviceId());
			if (!$check_pass) {
				throw new \Exception('密码修改成功，登录异常，请联系客服4008949580', 401);
			}
		}

		$response = $this->getCustomerInfo($customer);

		return $response;

    }

    public static function request()
    {
        return new ResetPasswordRequest();
    }

    public static function response()
    {
        return new CustomerResponse();
    }


}