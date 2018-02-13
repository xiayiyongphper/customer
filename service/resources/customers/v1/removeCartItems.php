<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/27
 * Time: 12:24
 */

namespace service\resources\customers\v1;

use common\models\ShoppingCart;
use service\message\customer\RemoveCartItemsRequest;
use service\models\common\Customer;
use common\models\LeCustomers;
use service\models\common\CustomerException;

/**
 * Author: Jason Y. Wang
 * Class updateItems
 * @package service\resources\customers
 */
class removeCartItems extends Customer
{
    public function run($data){
        /** @var RemoveCartItemsRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(), $request->getAuthToken());
        if($customer){
            $cart = new ShoppingCart($customer);
            $cart->removeCartItems($request->getProducts());
        }else{
            CustomerException::customerAuthTokenExpired();
        }
    }

    public static function request(){
        return new RemoveCartItemsRequest();
    }

    public static function response(){
        // 实际上没有包体,头里面的code代表成功与否
        return true;
    }
}