<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 18:31
 */

namespace service\resources\customers\v1;

use common\models\DeviceToken;
use framework\components\ToolsAbstract;
use service\message\customer\TestReportRequest;
use service\models\common\Customer;

/**
 * Class logout
 * 退出登录接口
 * @package service\resources\customers\v1
 */
class logout extends Customer
{

    /**
     * @param string $data
     * @return bool
     */
    public function run($data)
    {
        if ($this->getCustomerId()) {
            if ($token = DeviceToken::findDeviceTokenByCustomerId($this->getCustomerId())) {
                $token->setScenario(DeviceToken::SCENARIO_LOGOUT);
                $token->token = '';
                $token->save();
            }
        }
        return true;
    }

    public static function request()
    {
        return new TestReportRequest();
    }

    public static function response()
    {
        return true;
    }

}