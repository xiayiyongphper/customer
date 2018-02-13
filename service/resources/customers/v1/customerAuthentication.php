<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 16:51
 */

namespace service\resources\customers\v1;


use common\components\UserTools;
use common\models\LeCustomers;
use Exception;
use service\components\Tools;
use service\message\customer\CustomerAuthenticationRequest;
use service\message\customer\CustomerResponse;
use service\models\common\Customer;
use service\models\common\CustomerException;
use yii\base\Model;

class customerAuthentication extends Customer
{

    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return \service\message\customer\CustomerResponse
     * @throws Exception
     */
    public function run($data)
    {

        /** @var CustomerAuthenticationRequest $request */
        $request = self::parseRequest($data);

        // 为真则强制走数据库不取缓存数据
        $no_cache = $request->getNoCache();

//        Tools::log($request->getCustomerId(), 'customerAuthentication.log');
//        Tools::log($request->getAuthToken(), 'customerAuthentication.log');

        // 先看缓存里的token是否符合
        $customerArray = [];
        if (!$no_cache) {
            $customer = Tools::getRedis()->hGet(LeCustomers::CUSTOMERS_INFO_COLLECTION, $request->getCustomerId());
            if ($customer) {
                $customerArray = unserialize($customer);
            }
        }

        // 如果缓存里的没匹配，则取数据库
        if (!$customerArray || $customerArray['auth_token'] != $request->getAuthToken()) {
            $customer = $this->getCustomerModel($request->getCustomerId(), $request->getAuthToken());
            $customerArray = $customer->toArray();
        }

        // 王洋的log
        if (!isset($customerArray['tag_id'])) {
            Tools::log($customerArray, 'customerAuthentication.log');
        }

        // 审核状态检查
        if (!isset($customerArray['status'])) {
            CustomerException::customerSystemError();
        }
        // 非审核通过
        if ($customerArray['status'] != 1) {
            // 状态有问题，踢掉登录态
            if (!isset($customer) || !($customer instanceof LeCustomers)) {
                /* @var LeCustomers $customer */
                $customer = LeCustomers::findByCustomerId($customerArray['entity_id']);
            }
            $new_token = UserTools::getRandomString(16);
            $customer->auth_token = $new_token;
            $customer->save();
            // 写入缓存
            $customerArray['auth_token'] = $new_token;
            Tools::getRedis()->hSet(LeCustomers::CUSTOMERS_INFO_COLLECTION, $request->getCustomerId(), serialize($customerArray));

            // 还未审核
            if ($customerArray['status'] == 0) {
                CustomerException::notCheck();
            } // 审核不通过
            elseif ($customerArray['status'] == 2) {
                CustomerException::checkFail();
            }
            // 未知
            CustomerException::customerSystemError();
        }

        // 省市验证
        if (!isset($customerArray['province']) || !isset($customerArray['city'])
            || !$customerArray['province'] || !$customerArray['city']
        ) {
            CustomerException::customerInfoEmpty();
        }

        // 写入缓存
        Tools::getRedis()->hSet(LeCustomers::CUSTOMERS_INFO_COLLECTION, $request->getCustomerId(), serialize($customerArray));
        $response = $this->getCustomerBriefInfo($customerArray);
        //Tools::log($response,'wangyang.log');
        return $response;

    }

    public static function request()
    {
        return new CustomerAuthenticationRequest();
    }

    public static function response()
    {
        return new CustomerResponse();
    }

}