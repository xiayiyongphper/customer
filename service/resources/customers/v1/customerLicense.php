<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 16:51
 */

namespace service\resources\customers\v1;

use Exception;
use service\message\customer\CustomerAuthenticationRequest;
use service\models\common\Customer;

class customerLicense extends Customer
{

    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return bool|mixed
     * @throws Exception
     */
    public function run($data){
        /** @var CustomerAuthenticationRequest $request */
        $request = self::parseRequest($data);
        return $this->getCustomerLicense($request->getCustomerId(),$request->getAuthToken());
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }

    public static function request()
    {
        return new CustomerAuthenticationRequest();
    }
}