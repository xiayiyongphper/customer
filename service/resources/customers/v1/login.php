<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 18:31
 */

namespace service\resources\customers\v1;

use common\components\UserTools;
use common\models\Dau;
use common\models\LeCustomers;
use framework\components\ToolsAbstract;
use service\components\Tools;
use service\message\common\SourceEnum;
use service\message\customer\CustomerResponse;
use service\message\customer\LoginRequest;
use service\models\common\Customer;
use service\models\common\CustomerException;
use yii\base\Exception;

class login extends Customer
{
    /**
     * 默认设备最大登录账号数
     */
    const DEFAULT_MAX_LOGIN_ACOUNT_COUNT_PER_DEVICE = 3;

    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return CustomerResponse
     * @throws Exception
     * @throws \Exception
     */
    public function run($data)
    {
        /** @var LoginRequest $request */
        $request = self::parseRequest($data);
        if (empty($request->getUsername())) {
            CustomerException::customerNotExist();
        }


        /* 判断用户和密码 */
        try {
            $customer = LeCustomers::login($request->getUsername(), $request->getPassword());
        } catch (\Exception $e) {
            // 登陆失败
            // debug
            if ($e->getCode() == 102) {
                // 密码错
                /** @var LeCustomers $customer */
                $customer = LeCustomers::find()->where(['username' => $request->getUsername()])
                    ->orWhere(['phone' => $request->getUsername()])->one();
                // 找到用户则查看是否需要debug
                $this->checkIfCustomerNeedsDebug($customer);
            }
            /* DAU 登陆失败 */
            if (!empty($this->getDeviceId())) {
                $dau = new Dau();
                $dau->type = Dau::TYPE_LOGIN_FAIL;
                $dau->device_id = $this->getDeviceId();
                $dau->phone = $request->getUsername();
                $dau->created_at = ToolsAbstract::getDate()->date();
                Tools::log($dau->toArray());
                $dau->save();
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }


        // debug
        // 找到用户则查看是否需要debug
        $this->checkIfCustomerNeedsDebug($customer);


        /* 设备登录次数 */
        if (!empty($this->getDeviceId())) {
            $check_pass = $customer->checkLoginDevice($this->getDeviceId());
            if (!$check_pass) {
                throw new \Exception('登录受限，请联系客服4008949580', 401);
            }
        }

        /*
         * 针对2.7.0以下IOS客户端坑爹判断逻辑的修改
         * 2.7.0以下，ios客户端判断用户状态status（状态：0代表未审核，1代表审核通过，2代表审核不通过）的逻辑坑爹
         * 坑爹逻辑为，status非0，则视为审核通过
         * 所以要针对这部分的客户端，如果status为2，直接抛异常，弹toast
         */
        $app_version = $this->getAppVersion();
        $source = $this->getSource();
        if ($source == SourceEnum::IOS_SHOP
            && version_compare($app_version, '2.7.0', '<')
            && $customer->status == 2
        ) {
            throw new \Exception('审核不通过，请联系客服4408949580', 401);
        }

        /* DAU 登陆成功 */
        if (!empty($this->getDeviceId())) {
            $dau = new Dau();
            $dau->type = Dau::TYPE_LOGIN_SUCCESS;
            $dau->device_id = $this->getDeviceId();
            $dau->customer_id = $customer->getId();
            $dau->phone = $customer->phone;
            $dau->created_at = ToolsAbstract::getDate()->date();
            $dau->save();
        }

        /**
         * @author: henryzhu
         * 重新生成auth_token场景（重新生成token表示互踢）：
         * 1.用户当前没有auth_token
         * 2.用户的refresh_auth_token状态为1
         */
        if (!$customer->auth_token || $customer->refresh_auth_token == LeCustomers::REFRESH_AUTH_TOKEN_YES) {
            $customer->auth_token = UserTools::getRandomString(16);
            $customer->save();
        }

        $response = $this->getCustomerInfo($customer);

        // 重新登录后刷新缓存
        Tools::getRedis()->hDel(LeCustomers::CUSTOMERS_INFO_COLLECTION, $customer->entity_id);
        // 推送登录事件
        \framework\components\mq\Customer::publishLoginEvent(['entity_id' => $customer->entity_id]);

        return $response;
    }

    public function checkIfCustomerNeedsDebug($customer)
    {
        if (ToolsAbstract::isCustomerDebug($customer->entity_id)) {
            $this->getRequest()->getMessage()->getHeader()->setCustomerId($customer->entity_id);
            $this->getRequest()->setDebug(true);
        }
    }

    public static function request()
    {
        return new LoginRequest();
    }

    public static function response()
    {
        return new CustomerResponse();
    }

}