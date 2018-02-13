<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 15:42
 */

namespace service\resources\customers\v1;


use common\models\LeCustomers;
use Exception;
use service\message\common\SourceEnum;
use service\message\customer\CustomerDetailRequest;
use service\message\customer\CustomerResponse;
use service\models\common\Customer;
use service\models\common\CustomerException;

class customerDetail extends Customer
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return \service\message\customer\CustomerResponse
     * @throws Exception
     */
    public function run($data){
        /** @var CustomerDetailRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(),$request->getAuthToken());
        if(!$customer){
			CustomerException::customerAuthTokenExpired();
        }

		/*
		 * 针对2.7.0以下IOS客户端坑爹判断逻辑的修改
		 * 2.7.0以下，ios客户端判断用户状态status（状态：0代表未审核，1代表审核通过，2代表审核不通过）的逻辑坑爹
		 * 坑爹逻辑为，status非0，则视为审核通过
		 * 所以要针对这部分的客户端，如果status为2，直接抛异常，弹toast
		 */
		$app_version = $this->getAppVersion();
		$source = $this->getSource();
		if( $source == SourceEnum::IOS_SHOP
			&& version_compare($app_version, '2.7.0', '<')
			&& $customer->status==2
		){
			throw new \Exception('审核不通过，请联系客服4408949580', 401);
		}


		$response = $this->getCustomerInfo($customer);
		return $response;
    }

    public static function request(){
        return new CustomerDetailRequest();
    }

    public static function response(){
        return new CustomerResponse();
    }
}