<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 16:20
 */

namespace service\resources\customers\v1;


use common\models\LeCustomers;
use common\models\LeCustomersAddressBook;
use Exception;
use service\message\customer\ChangeReceiverInfoRequest;
use service\message\customer\CustomerResponse;
use service\models\common\Customer;
use service\models\common\CustomerException;
use framework\components\mq\Customer as MQCustomer;


class changeReceiverInfo extends Customer
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
        /** @var ChangeReceiverInfoRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(),$request->getAuthToken());

		// Token失效
		if(!$customer){
			CustomerException::customerAuthTokenExpired();
		}

		//保存收货人信息
		/* @var $addressBook LeCustomersAddressBook */
		$addressBook = new LeCustomersAddressBook();
		$addressBook = $addressBook->findReceiverCustomerId($customer->getId());
		// 新建收货人信息
		if(!$addressBook){
			$addressBook = new LeCustomersAddressBook();
			$addressBook->customer_id = $customer->entity_id;
			$addressBook->created_at = date('Y-m-d H:i:s');
		}
		// 更新收货人信息
		$addressBook->receiver_name = $request->getReceiverName();
		$addressBook->phone = $request->getReceiverPhone();
		$addressBook->updated_at = date('Y-m-d H:i:s');
		$addressBook->save();

		// 整理返回
		$response = $this->getCustomerInfo($customer);

		/* 修改成功消息通知 */
		MQCustomer::publishUpdateEvent($response->toArray());

		return $response;
    }

    public static function request()
    {
        return new ChangeReceiverInfoRequest();
    }

    public static function response()
    {
		return new CustomerResponse();
    }
}