<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 14:32
 */

namespace service\resources\customers\v1;


use common\models\LeCustomers;
use service\message\customer\CustomerResponse;
use service\message\customer\FillCustomerInfoRequest;
use service\models\common\CustomerException;
use yii\base\Exception;
use service\models\common\Customer;
use framework\components\mq\Customer as MQCustomer;

class changeCustomerInfo extends Customer
{

    /**
     * Function: run
     * Author: Jason Y. Wang
     * 保存用户信息
     * @param $data
     * @return \service\message\customer\CustomerResponse
     * @throws Exception
     */
    public function run($data){
        /** @var FillCustomerInfoRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(),$request->getAuthToken());

        // Token失效
		if(!$customer){
			CustomerException::customerAuthTokenExpired();
		}

		// 保存
		$customer->store_name = $request->getStoreName();
		$customer->storekeeper = $request->getStoreKeeper();
		$customer->store_area = $request->getStoreArea();
		$customer->business_license_img = $request->getBusinessLicenseImg();
		$customer->updated_at = date('Y-m-d H:i:s');

		// 保存失败
		if(!$customer->save()){
			CustomerException::customerSystemError();
		}

		// 至此保存成功
		$response = $this->getCustomerInfo($customer);

		/* 修改成功消息通知 */
		MQCustomer::publishUpdateEvent($response->toArray());

		return $response;

    }

    public static function request()
    {
        return new FillCustomerInfoRequest();
    }

    public static function response()
    {
		return new CustomerResponse();
    }
}