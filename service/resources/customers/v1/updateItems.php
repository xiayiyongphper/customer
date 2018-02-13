<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/27
 * Time: 12:24
 */

namespace service\resources\customers\v1;

use common\models\ShoppingCart;
use service\components\Tools;
use service\message\customer\UpdateCartItemsRequest;
use service\models\common\Customer;
use common\models\LeCustomers;
use service\models\common\CustomerException;

/**
 * Author: Jason Y. Wang
 * Class updateItems
 * @package service\resources\customers
 */
class updateItems extends Customer
{
    public function run($data){
        /** @var UpdateCartItemsRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(), $request->getAuthToken());
        if($customer->entity_id == 2492){
            Tools::log($request,'wangyang.log');
        }
        if($customer){
            $cart = new ShoppingCart($customer);
            $cart->updateCartItems($request->getProducts());
        }else{
            CustomerException::customerAuthTokenExpired();
        }
    }

    public static function request(){
        return new UpdateCartItemsRequest();
    }

    public static function response(){
        // 实际上没有包体,头里面的code代表成功与否
        return true;
    }

}