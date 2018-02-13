<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/8
 * Time: 18:31
 */
namespace service\resources\driver\v1;

use common\components\DriverSms;
use common\components\UserTools;
use common\models\driver\Driver;
use common\models\VerifyCode;
use service\message\driver\DriverInfo;
use service\message\driver\LoginRequest;
use service\models\common\ContractorException;
use service\models\common\DriverAbstract;
use service\models\common\DriverException;

class login extends DriverAbstract
{

    public function run($data)
    {
        /** @var LoginRequest $request */
        $request = self::parseRequest($data);
        $phone = $request->getPhone();
        $login_type = $request->getLoginType();
        /** @var Driver $driver */
        $driver = Driver::findByPhone($request->getPhone());

        if(!$driver){
            DriverException::driverNotExist();
        }

        $driver_model = false;
        switch ($login_type){
            case 1:
                $driver_model = $this->smsLogin($phone,$request->getCode());
                break;
            case 2:
                $driver_model = $this->passwordLogin($phone,$request->getPassword());
                break;
            default:
                DriverException::driverSmsTypeInvalid();
                break;
        }

        if(!$driver_model){
            DriverException::driverSystemError();
        }

        $driver_response = $this->getDriverInfo($phone);
        return $driver_response;

    }

    /**
     * @param $phone
     * @param $code
     * @return bool
     * @throws DriverException
     */
    public function smsLogin($phone,$code){
        /** @var Driver $driver */
        $driver = Driver::findByPhone($phone);
        /** @var  VerifyCode $verify */
        $verify = VerifyCode::find()->where(['phone' => $phone, 'verify_type' => DriverSms::DRIVER_SMS_TYPE_LOGIN])->orderBy(['created_at' => SORT_DESC])->one();
        if (!$verify) {
            DriverException::verifyCodeError();
        }

        if(isset($verify['created_at']) && strtotime($verify['created_at']) + 60 < time()){
            ContractorException::verifyCodeAlreadyExpired();
        }

        if(isset($verify['code']) && $code == $verify['code']) {
            $auth_token = UserTools::getRandomString(16);
            $driver->auth_token = $auth_token;
            if ($driver->save()) {
                return $driver;
            }
        }
        return false;
    }

    public function passwordLogin($phone,$password){
        //前面已验证，这个phone一定会存在
        $driver = Driver::loginByPhonePassword($phone,$password);
        if(!$driver){
            DriverException::driverPasswordError();
        }
        $auth_token = UserTools::getRandomString(16);
        $driver->auth_token = $auth_token;
        return $driver->save();
    }

    public static function request(){
        return new LoginRequest();
    }

    public static function response(){
        return new DriverInfo();
    }

}