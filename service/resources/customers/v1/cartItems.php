<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/26
 * Time: 10:59
 */

namespace service\resources\customers\v1;


use common\models\ShoppingCart;
use service\message\customer\CartItemsRequest;
use common\models\LeCustomers;
use service\message\customer\CartItemsResponse;
use service\models\common\Customer;
use service\models\common\CustomerException;

class cartItems extends Customer
{
    public function run($data){
        /** @var CartItemsRequest $request */
        $request = self::parseRequest($data);
        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(), $request->getAuthToken());
        if(!$customer){
           CustomerException::customerAuthTokenExpired();
        }
        $cart = new ShoppingCart($customer);
        $cartItems = $cart->formatShoppingCart();
        return $cartItems;
    }

    public static function request(){
        return new CartItemsRequest();
    }

    public static function response(){
        return new CartItemsResponse();
    }
}