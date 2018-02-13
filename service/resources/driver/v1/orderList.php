<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 11:31
 */

namespace service\resources\driver\v1;

use common\components\UserTools;
use common\models\driver\Order;
use service\components\Tools;
use service\message\common\Pagination;
use service\message\driver\OrderListRequest;
use service\message\driver\OrderListResponse;
use service\models\common\DriverAbstract;

class orderList extends DriverAbstract
{
    /**
     * Function: run
     * Author: Jason Y. Wang
     *
     * @param $data
     * @return mixed
     */
    public function run($data)
    {
        /** @var OrderListRequest $request */
        $request = self::parseRequest($data);
        $paginationRequest = $request->getPagination();
        $list_type = $request->getListType()?:0;
        $keyword = $request->getKeyword();

        $driver = $this->initDriver($request);
        $page = 1;
        $pageSize = 10;
        if($paginationRequest){
            $page = $paginationRequest->getPage()?:1;
            $pageSize = $paginationRequest->getPageSize()?:10;
        }

        $order_ids_collection = Order::getOrderIdsByDriver($driver,$list_type,$keyword);
        //Tools::log($order_ids_collection,'wangyang.log');
        //偏移翻页
        $order_ids = array_slice($order_ids_collection,($page-1)*$pageSize,$pageSize);

        $paginationResponse = new Pagination();
        $paginationResponse->setPage($page);
        $paginationResponse->setPageSize($pageSize);
        $paginationResponse->setTotalCount(count($order_ids_collection));
        $paginationResponse->setLastPage(ceil(count($order_ids_collection)/$pageSize));
        //Tools::log($order_ids,'wangyang.log');
        if(count($order_ids) == 0){
            return true;
        }

        $orders = UserTools::getOrderInfoCollectionsByProxy($order_ids);

        //Tools::log($orders,'wangyang.log');

        if(!$orders){
            return true;
        }

        $response = self::response();

        $driverOrderArray = [];
        /** @var \service\message\common\Order $order */
        foreach($orders as $order){
            //是否显示删除按钮
            if(in_array($order->getStatus(),['canceled','hold','processing','closed','rejected_closed'])){
                $show_delete = 1;
            }else{
                $show_delete = 0;
            }
            /** @var Order $driverOrder */
            $driverOrder = Order::getByOrderId($order->getOrderId());
            if($driverOrder){
                $driverOrder = $this->convertDriverArray($driverOrder);
                $driverOrder['show_delete'] = $show_delete;
                $driverOrder['order'] = $order->toArray();
                $driverOrderArray[] = $driverOrder;
            }
        }

        $orderListResponse = [
            'items' => $driverOrderArray,
        ];
        $response->setPagination($paginationResponse);
        //Tools::log($orderListResponse,'wangyang.log');
        $response->setFrom($orderListResponse);
        return $response;

    }

    public static function request(){
        return new OrderListRequest();
    }

    public static function response(){
        return new OrderListResponse();
    }
}