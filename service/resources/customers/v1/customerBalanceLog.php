<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 15:42
 */

namespace service\resources\customers\v1;


use common\models\LeCustomers;
use common\models\LeCustomersBalanceLog;
use framework\components\Date;
use framework\components\ToolsAbstract;
use service\components\Tools;
use framework\data\Pagination;
use service\message\customer\CustomerBalanceLogRequest;
use service\message\customer\CustomerBalanceLogResponse;
use service\models\common\Customer;
use service\models\common\CustomerException;
use Exception;

class customerBalanceLog extends Customer
{
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * Function: run
     * Author: zgr
     *
     * @param $data
     * @return \service\message\customer\CustomerBalanceLogResponse
     * @throws Exception
     */
    public function run($data){
        /** @var CustomerBalanceLogRequest $request */
        $request = self::parseRequest($data);
        $pageSize = self::DEFAULT_PAGE_SIZE;
        $page = $request->getPage() ? $request->getPage() : 1;

        /* @var LeCustomers $customer */
        $customer = $this->getCustomerModel($request->getCustomerId(),$request->getAuthToken());
        if(!$customer){
            CustomerException::customerAuthTokenExpired();
        }

        // 查找
        $query = LeCustomersBalanceLog::find();
        $query->where(['customer_id'=>$customer->getId()]);

        // 分页
        $countQuery = clone $query;

        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->setCurPage($page);
        $pages->setPageSize($pageSize);

        $res = $query->offset($pages->getOffset())
            ->limit($pages->getLimit())
            ->orderBy(["created_at" => SORT_DESC])
            ->all();

		$date = new Date();
        $balance_log = [];
        foreach ($res as $item) {
            $one_log = $item->toArray();
			$one_log['created_at'] = $date->date('Y-m-d H:i:s', $one_log['created_at']);// 时区问题
            //$one_log['amount'] = Tools::formatPrice($one_log['amount']);
            //$one_log['balance'] = Tools::formatPrice($one_log['balance']);
            array_push($balance_log, $one_log);
        }

        $response = self::response();
        $response->setFrom(ToolsAbstract::pb_array_filter([
            'balance_log'=>$balance_log,
            'pagination' => [
                'total_count' => $pages->getTotalCount(),
                'page' => $pages->getCurPage(),
                'last_page' => $pages->getLastPageNumber(),
            ],
        ]));
        return $response;


    }

    public static function request(){
        return new CustomerBalanceLogRequest();
    }

    public static function response(){
        return new CustomerBalanceLogResponse();
    }
}