<?php

namespace service\resources\customers\v1;

use service\models\customer\Observer;
use service\resources\ResourceAbstract;

class consumeBalanceByJson extends ResourceAbstract
{
    public function run($data)
    {
        $requestData = json_decode($data, true);
        Observer::balance_consume($requestData);
    }

    public static function request()
    {
        return true;
    }

    public static function response()
    {
        return true;
    }
}