<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 17:16
 */

namespace service\resources\customers\v1;


use common\models\LeCustomerComplaint;
use common\models\LeCustomers;
use Exception;
use service\components\Tools;
use service\message\customer\CustomerComplaintRequest;
use service\models\common\Customer;

class customerComplaint extends Customer
{

    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function run($data){
        /** @var CustomerComplaintRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(),$request->getAuthToken());
        if($customer){
            /* @var $complaint LeCustomerComplaint */
            $complaint = new LeCustomerComplaint();
            $complaint->customer_id = $request->getCustomerId();
            $complaint->wholesaler_id = $request->getWholesalerId();
            $complaint->increment_id = $request->getIncrementId();
            $complaint->contact_name = $request->getContactName();
            $complaint->contact_phone = $request->getContactPhone();
            $complaint->type = $request->getType();
            $complaint->content = $request->getContent();
            $complaint->created_at = date('Y-m-d H:i:s');
            if(!$complaint->save()){
                throw new Exception('投诉保存失败',2001);
            }
        }else{
            throw new Exception('用户登陆状态过期',2001);
        }
    }

    public static function request()
    {
        return new CustomerComplaintRequest();
    }

    public static function response()
    {
        // TODO: Implement response() method.
    }
}