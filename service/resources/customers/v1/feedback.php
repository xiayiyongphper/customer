<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/2/15
 * Time: 17:42
 */

namespace service\resources\customers\v1;

use common\models\LeContacts;
use common\models\LeCustomers;
use Exception;
use service\message\customer\FeedbackRequest;
use service\models\common\Customer;
use service\models\common\CustomerException;

class feedback extends Customer
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
        /** @var FeedbackRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(),$request->getAuthToken());
        if($customer){
            $feedback = new LeContacts();
            $feedback->user_name = $request->getUserName();
            $feedback->phone = $request->getPhone();
            $feedback->user_id = $request->getCustomerId();
            $feedback->content = htmlspecialchars($request->getContent());
            $feedback->typevar = htmlspecialchars($request->getTypevar());
            $feedback->type_id = $request->getTypeId();
            $feedback->create_at = date('Y-m-d H:i:s');
            if($feedback->save()){
                return true;
            }
        }else{
            CustomerException::customerAuthTokenExpired();
        }
    }

    public static function request()
    {
        return new FeedbackRequest();
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }

}