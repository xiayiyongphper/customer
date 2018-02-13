<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 15:45
 */

namespace service\resources\customers\v1;

use common\components\CustomerSms;
use common\models\VerifyCode;
use service\message\customer\ChangeBindingPhoneRequest;
use service\message\customer\CustomerResponse;
use service\models\common\Customer;
use common\models\LeCustomers;
use service\models\common\CustomerException;
use framework\components\mq\Customer as MQCustomer;


class changeBindingPhone extends Customer
{

    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param \ProtocolBuffers\Message $data
     * @return \service\message\customer\CustomerResponse
     * @throws CustomerException
     */
    public function run($data){
        /** @var ChangeBindingPhoneRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(),$request->getAuthToken());

        // Token失效
		if(!$customer) {
			CustomerException::customerAuthTokenExpired();
		}

		/** @var VerifyCode $verify */
		$verify = VerifyCode::find()
			->where(['phone' => $request->getPhone(), 'verify_type' => CustomerSms::CUSTOMER_SMS_TYPE_CHANGE_BINDING_PHONE])
			->orderBy(['created_at' => SORT_DESC])
			->one();
		// 验证码不对
		if (!$verify || $verify['code'] != $request->getCode()) {
			CustomerException::verifyCodeError();
		}

		// 保存
		$customer->phone = $request->getPhone();

		// 保存失败
		if (!$customer->save()) {
			CustomerException::changeBindingPhoneError();
		}

		$response = $this->getCustomerInfo($customer);

		/* 修改成功消息通知 */
		MQCustomer::publishUpdateEvent($response->toArray());

		return $response;


    }

    public static function request()
    {
        return new ChangeBindingPhoneRequest();
    }

    public static function response()
    {
		return new CustomerResponse();
    }
}