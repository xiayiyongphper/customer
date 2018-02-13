<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 14:32
 */

namespace service\resources\customers\v1;


use common\components\UserTools;
use common\models\LeCustomers;
use common\models\LeCustomersAddressBook;
use service\message\customer\FillCustomerInfoRequest;
use service\models\common\CustomerException;
use yii\base\Exception;
use service\models\common\Customer;

class saveCustomerInfo extends Customer
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
        if($customer){
            $customer->province = $request->getProvince();
            $customer->city = $request->getCity();
            $customer->district = $request->getDistrict();
            $customer->address = $request->getAddress();
            $customer->detail_address = $request->getDetailAddress();
            $customer->store_name = $request->getStoreName();
            $customer->storekeeper = $request->getStoreKeeper();
            //根据经纬度判断超市所在的区块ID
            $customer->area_id = UserTools::findAreaIdByLocation($request->getLat(),$request->getLng());
            $customer->lat = $request->getLat();
            $customer->lng = $request->getLng();
            $customer->status = 0;
            $customer->updated_at = date('Y-m-d H:i:s');
            if($customer->save()){
                //保存用户的收货人信息
                if($receiver = LeCustomersAddressBook::findReceiverCustomerId($customer->getId())){
                    $receiver->phone = $customer->phone;
                    $receiver->receiver_name = $customer->storekeeper;
                    $receiver->updated_at = date('Y-m-d H:i:s');
                }else{
                    $receiver = new LeCustomersAddressBook();
                    $receiver->customer_id = $customer->getId();
                    $receiver->phone = $customer->phone;
                    $receiver->receiver_name = $customer->storekeeper;
                    $receiver->created_at = date('Y-m-d H:i:s');
                    $receiver->updated_at = date('Y-m-d H:i:s');
                }
                if(!$receiver->save()){
                    CustomerException::customerSystemError();
                }
                $response = $this->getCustomerInfo($customer);
                return $response;
            }else{
                CustomerException::customerSystemError();
            }
        }else{
            CustomerException::customerAuthTokenExpired();
        }
    }

    public static function request()
    {
        return new FillCustomerInfoRequest();
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }

}