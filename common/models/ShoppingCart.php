<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/26
 * Time: 11:03
 */

namespace common\models;
use common\components\UserTools;
use service\components\Tools;
use service\message\common\Product;
use service\message\customer\CartItemsResponse;
use service\message\common\Store;
use service\models\common\CustomerException;

/**
 * Author: Jason Y. Wang
 * Class LE_Sales_Model_ShoppingCart
 */
class ShoppingCart
{
    //购物车前缀
    const CART_KEY_PREFIX = 'cart_key';
    //用户在Redis购物车中的key
    protected $cart_key;
    //Redis中的购物车原型
    protected $cart_items;
    /** @var LeCustomers $customer*/
    protected $customer;

    /**
     * @param  $customer
     * @throws CustomerException
     */
    public function __construct(LeCustomers $customer)
    {
        if ($customer) {
            $this->customer = $customer;
            //获取Redis中某个用户购物车的key
            $this->cart_key = sprintf('%s_%s_%s', self::CART_KEY_PREFIX, $customer->getId(), $customer->area_id);
            //在Redis中取得数据
            $this->cart_items = unserialize(Tools::getRedis()->get($this->cart_key));
        } else {
            CustomerException::customerAuthTokenExpired();
        }
    }

    /**
     * Function: loadShoppingCartByCustomer
     * Author: Jason Y. Wang
     * 格式化redis中的购物车
     * @return array
     */
    public function formatShoppingCart()
    {
        $data = array();
        $cartItems = new CartItemsResponse();
        if(!$this->cart_items){
            return $cartItems;
        }

        //格式化购物车中的商品
        foreach ($this->cart_items as $wholesaler_id => $products) {
            if(!is_numeric($wholesaler_id)){
                //去掉key不是数字的数据，避免购物车数据混乱错误导到的问题
                if($this->customer->entity_id == 2492){
                    Tools::log($wholesaler_id,'wangyang.log');
                }
                continue;
            }
            //初始化店铺
            /** @var Store $storeDetailResponse */
            $store = UserTools::getStoreInfoByProxy($wholesaler_id);

            if (!$store) {
                //如果load不到店铺，则不处理这个商品
                continue;
            }

            $productIds = array_keys($products['list']);
            $wholesaler_cart = array();

            $productList = UserTools::getProductsByProxy($this->customer->city,$productIds);
            if(!$productList){
                //抛出异常时，不处理这个商品
                continue;
            }
            $productsTmp = array();
            /** @var Product $product */
            foreach($productList as $product){
                $product_num = $products['list'][$product->getProductId()]['num'];
                $product_info = [
                    'product_id' => $product->getProductId(),
                    'name' => $product->getName(),
                    'url' => $product->getUrl(),
                    'image' => $product->getImage(),
                    'gallery' => $product->getGallery(),
                    'price' => $product->getPrice(),
                    'special_price' => $product->getSpecialPrice(),
                    'original_price' => $product->getOriginalPrice(),
                    'discount' => $product->getDiscount(),
                    'sold' => $product->getSold(),
                    'qty' => $product->getQty(),
                    'specification' => $product->getSpecification(),
                    'wholesaler_id' => $product->getWholesalerId(),
                    'wholesaler_name' => $product->getWholesalerName(),
                    'wholesaler_url' => $product->getWholesalerUrl(),
                    'num' => $product_num,
                ];
                //去掉为空的属性
                $product_info = array_filter($product_info);
                $productsTmp[$product->getProductId()] = $product_info;
            }

            //起送价向上取整
            $min_trade_amount = ceil($store->getMinTradeAmount());
            $wholesaler_cart['list'] = $productsTmp;
            $wholesaler_cart['min_trade_amount'] = $min_trade_amount;
            $wholesaler_cart['wholesaler_id'] = $store->getWholesalerId();
            $wholesaler_cart['wholesaler_name'] = $store->getWholesalerName();
            $wholesaler_cart['tips'] = '满' . $min_trade_amount . '元起送';
            $wholesaler_cart = array_filter($wholesaler_cart);
            $data[] = $wholesaler_cart;
        }
        $cartItemsData = array('data' => $data);
        $cartItems->setFrom(Tools::pb_array_filter($cartItemsData));
        //返回数组
        return $cartItems;
    }

    /**
     * Function: loadShoppingCartByCustomer
     * Author: Jason Y. Wang
     * 格式化redis中的购物车
     * @return array
     */
    public function formatShoppingCartV2()
    {
        $data = array();
        $cartItems = new CartItemsResponse();
        if(!$this->cart_items){
            return $cartItems;
        }
        //格式化购物车中的商品
        foreach ($this->cart_items as $wholesaler_id => $products) {
            if(!is_numeric($wholesaler_id)){
                //去掉key不是数字的数据，避免购物车数据混乱错误导到的问题
                continue;
            }

            $productIds = array_keys($products['list']);

            $wholesaler_cart = array();

            $productsList = array();
            /** @var Product $product */
            foreach($productIds as $productId){
                $product_num = $products['list'][$productId]['num'];
                $product_info = [
                    'product_id' => $productId,
                    'num' => $product_num,
                ];
                //去掉为空的属性
                $product_info = array_filter($product_info);
                $productsList[$product->getProductId()] = $product_info;
            }

            $wholesaler_cart['list'] = $productsList;
            $wholesaler_cart = array_filter($wholesaler_cart);
            $data[] = $wholesaler_cart;
        }
        $cartItemsData = array('data' => $data);
        //Tools::log($cartItemsData,'wangyang.log');
        $cartItems->setFrom($cartItemsData);
        //返回数组
        return $cartItems;
    }

    /**
     * Function: updateCartItems
     * Author: Jason Y. Wang
     * 更新购物车中的商品信息
     * @param $products
     * @throws CustomerException
     */
    public function updateCartItems($products)
    {
        //修改redis数据
        foreach ($products as $key => $product) {
            /** @var Product $product */
            $wholesaler_id = $product->getWholesalerId();
            $product_id = $product->getProductId();
            $num = $product->getNum();
            //传入数据为空时，不做处理 num只处理数字
            if(!isset($wholesaler_id) || $wholesaler_id === ''
                || !isset($product_id) || $product_id === ''
                || !isset($num) || $num === '' || !is_numeric($num)
            ){
                CustomerException::updateCartParamError();
            }
            /** @var Store $store */
            $store = UserTools::getStoreInfoByProxy($wholesaler_id);
            if (!$store) {
                //如果load不到店铺，则不处理这个商品
                continue;
            }
            //得到商品模型
            $product_response = UserTools::getProductsByProxy($this->customer->city,[$product_id]);
            $product_response = $product_response[0];
            if (!$product_response) {
                //如果数据库中查不到这个商品，则不处理这个商品
                continue;
            }
            if($num > $product_response->getQty()){
                CustomerException::productQtyLow($product_response->getName().'库存不足');
            }
            //删除商品
            if ($num == 0){
                if (isset($this->cart_items[$wholesaler_id])) {
                    //购物车中已有店铺修改商品
                    if (isset($this->cart_items[$wholesaler_id]['list'][$product_id])) {
                        //删除商品
                        unset($this->cart_items[$wholesaler_id]['list'][$product_id]);
                        if (count($this->cart_items[$wholesaler_id]['list']) == 0) {
                            //是否删除店铺
                            unset($this->cart_items[$wholesaler_id]);
//                            if (count($this->cart_items) == 0) {
//                                Tools::getRedis()->delete($this->cart_key);
//                            }
                        }
                    }
                }
            }elseif ($num > 0) {
                //更新商品信息
                if (isset($this->cart_items[$wholesaler_id])) {
                    //购物车中已有店铺修改商品
                    if (isset($this->cart_items[$wholesaler_id]['list'][$product_id])) {
                        //修改已有商品   并将已修改的店铺放到购物车的第一位
                        $this->cart_items[$wholesaler_id]['list'][$product_id]['num'] = $num;
//                        $product = [$product_id => $this->cart_items[$wholesaler_id]['list'][$product_id]];
//                        $this->cart_items[$wholesaler_id]['list'] = $product + $this->cart_items[$wholesaler_id]['list'];
                        $wholesaler_cart = [$wholesaler_id => $this->cart_items[$wholesaler_id]];
                        $this->cart_items = $wholesaler_cart + $this->cart_items;
                        //Tools::log($this->cart_items,'wangyang.txt');
                    } else {
                        //新增商品，数量不能为0
                        $product_item = array(
                            'productId' => $product_response->getProductId(),
                            'barcode' => $product_response->getBarcode(),
                            'name' => $product_response->getName(),
                            'gallery' => $product_response->getGallery(),
                            'num' => $num
                        );
                        $this->cart_items[$wholesaler_id]['list'][$product_id] = $product_item;
                        $wholesaler_cart = [$wholesaler_id => $this->cart_items[$wholesaler_id]];
                        $this->cart_items = $wholesaler_cart + $this->cart_items;
                    }
                } else {
                    //购物车中新增店铺和商品
                    $wholesaler_name = $store->getWholesalerName();
                    //商品信息
                    $product_item = array(
                        'productId' => $product_response->getProductId(),
                        'barcode' => $product_response->getBarcode(),
                        'name' => $product_response->getName(),
                        'gallery' => $product_response->getGallery(),
                        'num' => $num
                    );
                    //店铺信息
                    $wholesaler_cart = array(
                        'name' => $wholesaler_name,
                        'list' => array(
                            $product_id => $product_item,
                        ),
                    );
                    $this->cart_items[$wholesaler_id] = $wholesaler_cart;
//                    $wholesaler_cart = [$wholesaler_id => $wholesaler_cart];
//                    $this->cart_items = $wholesaler_cart + $this->cart_items;
                }
            }
        }
        Tools::getRedis()->set($this->cart_key, serialize($this->cart_items));
    }

    /**
     * Function: updateCartItems
     * Author: Jason Y. Wang
     * 删除购物车中的商品信息
     * @param $products
     * @throws CustomerException
     */
    public function removeCartItems($products)
    {
        //修改redis数据
        foreach ($products as $key => $product) {
            /** @var Product $product */
            $wholesaler_id = $product->getWholesalerId();
            $product_id = $product->getProductId();

            //删除商品
            if (isset($this->cart_items[$wholesaler_id])) {
                if (isset($this->cart_items[$wholesaler_id]['list'][$product_id])) {
                    //删除商品
                    unset($this->cart_items[$wholesaler_id]['list'][$product_id]);
                    if (count($this->cart_items[$wholesaler_id]['list']) == 0) {
                        //是否删除店铺
                        unset($this->cart_items[$wholesaler_id]);
//                        if (count($this->cart_items) == 0) {
//                            Tools::getRedis()->delete($this->cart_key);
//                        }
                    }
                }
            }
        }
        Tools::getRedis()->set($this->cart_key, serialize($this->cart_items));
    }
}