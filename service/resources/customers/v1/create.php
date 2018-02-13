<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 11:59
 */
namespace service\resources\customers\v1;
use common\components\CustomerSms;
use common\components\UserTools;
use common\models\Dau;
use common\models\LeCustomers;
use common\models\RegisterDeviceToken;
use common\models\VerifyCode;
use framework\components\Security;
use framework\components\ToolsAbstract;
use service\components\Tools;
use service\message\customer\CustomerResponse;
use service\message\customer\RegisterRequest;
use service\models\common\Customer;
use service\models\common\CustomerException;
use service\models\customer\Observer;

class create extends Customer
{
	/** @var RegisterRequest $_request */
	protected $_request = null;

    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return \service\message\customer\CustomerResponse
     * @throws CustomerException
     * @throws \Exception
     */
    public function run($data){
        /** @var RegisterRequest $request */
        $request = self::parseRequest($data);
        $this->_request = $request;
        /** @var CustomerResponse $response */
        $response = self::response();

		//用户名不能为纯数字
		if(is_numeric($request->getUsername())){
			/* DAU 注册失败 */
			$this->dauFail();
			CustomerException::customerRegisterUsernameNumeric();
		}

        //判断验证码是否正确
        /** @var VerifyCode $verify */
        $verify = VerifyCode::find()
            ->where(['phone' => $request->getPhone(), 'verify_type' => CustomerSms::CUSTOMER_SMS_TYPE_REGISTER])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
		if (!$verify || $verify['code'] != $request->getCode()) {
			/* DAU 注册失败 */
			$this->dauFail();
			CustomerException::verifyCodeError();
		}

		// 用户名已注册
		if($request->getUsername() != '' && LeCustomers::checkUserByUserName($request->getUsername())){
			/* DAU 注册失败 */
			$this->dauFail();
			CustomerException::customerUsernameAlreadyRegistered();
		}

		// 手机已注册
		if(LeCustomers::checkUserByPhone($request->getPhone())){
			/* DAU 注册失败 */
			$this->dauFail();
			CustomerException::customerPhoneAlreadyRegistered();
		}

		/* 设备注册次数限制 */
		if (!empty($this->getDeviceId())) {
			$check_pass = LeCustomers::checkRegisterDevice($this->getDeviceId(), $request->getPhone());
			if ($check_pass==false) {
				/* DAU 注册失败 */
				$this->dauFail();
				throw new \Exception('注册受限，请联系客服4008949580', 401);
			}elseif($check_pass instanceof RegisterDeviceToken){
				$regDeviceToken = $check_pass;
			}else{
				// 设备id为空，直接pass了，不管
			}
		}

		// 创建用户
		/* @var LeCustomers $customer */
		$customer = new LeCustomers();
		$customer->username = $request->getUsername();
		$customer->phone = $request->getPhone();
		$customer->password = Security::passwordHash($request->getPassword());
		$customer->auth_token = UserTools::getRandomString(16);
		$customer->created_at = date('Y-m-d H:i:s');
		$customer->updated_at = date('Y-m-d H:i:s');
		$customer->data_debug = [
			'source' => $this->getSource(),
			'source_code' => $this->getSourceCode(),
			'app_version' => $this->getAppVersion(),
		];

		// 用户数据库保存失败
		if(!$customer->save()){
			/* DAU 注册失败 */
			$this->dauFail();
			/* 注册失败把设备注册记录设为已删除 */
			if (isset($regDeviceToken)) {
				$regDeviceToken->is_del = RegisterDeviceToken::DELETED;
				$regDeviceToken->save();
			}
			Tools::logException(print_r($customer->errors,true));
			CustomerException::customerRegisterFailed();
		}

		// 保存成功
		/* DAU 注册成功 */
		$this->dauSuccess($customer);
		/* 把设备注册记录里的手机号码更新为customer_id */
		if (isset($regDeviceToken)) {
			$regDeviceToken->customer_id = $customer->entity_id;
			$regDeviceToken->save();
		}

		$response = $this->getCustomerInfo($customer);

        return $response;
    }

	/**
	 * DAU 注册成功
	 * @param $customer LeCustomers
	 */
	protected function dauSuccess($customer){
		/* DAU 注册成功 */
		if (!empty($this->getDeviceId())) {
			$dau = new Dau();
			$dau->type = Dau::TYPE_REGISTER_SUCCESS;
			$dau->device_id = $this->getDeviceId();
			$dau->customer_id = $customer->getId();
			$dau->phone = $customer->phone;
			$dau->created_at = ToolsAbstract::getDate()->date();
			//Tools::log($dau->toArray());
			$dau->save();
		}
	}

	/*
	 * DAU 注册失败
	 */
	protected function dauFail(){
		/*  */
		if (!empty($this->getDeviceId())) {
			$dau = new Dau();
			$dau->type = Dau::TYPE_REGISTER_FAIL;
			$dau->device_id = $this->getDeviceId();
			$dau->phone = $this->_request->getPhone();
			$dau->created_at = ToolsAbstract::getDate()->date();
			//Tools::log($dau->toArray());
			$dau->save();
		}
	}

    public static function request()
    {
    	return new RegisterRequest();
    }

    public static function response()
    {
    	return new CustomerResponse();
    }
}