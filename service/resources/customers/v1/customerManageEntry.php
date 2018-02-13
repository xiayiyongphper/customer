<?php
/**
 * Created by PhpStorm.
 * Date: 2017/3/29
 * Time: 20:33
 */

namespace service\resources\customers\v1;


use common\models\LeCustomers;
use service\components\Tools;
use service\message\contractor\ContractorAuthenticationRequest;
use service\message\contractor\ManageResponse;
use service\models\common\Customer;
use service\models\common\CustomerException;

class customerManageEntry extends Customer
{
    public function run($data)
    {
        $request = self::request();
        $request->parseFromString($data);
        $response = self::response();
        $contractor_id = $request->getContractorId();
        $auth_token = $request->getAuthToken();
        $contractor = $this->_initContractor($contractor_id, $auth_token);
        $city = $request->getCity();
        if (!$city) {
            CustomerException::contractorCityEmpty();
        }
        if ($contractor->getRole() == self::COMMON_CONTRACTOR) {
            //注册未审核
            $reviewConditions = ['city' => $city, 'status' => 0];
            //注册未下单
            $noOrderConditions = ['contractor_id' => $contractor_id, 'city' => $city, 'status' => 1, 'first_order_id' => 0];
            //15天内没有拜访
            $visitDate = date('Y-m-d', strtotime('-15 days'));
            $noVisitConditions = ['and',['contractor_id' => $contractor_id, 'city' => $city, 'status' => 1],['<', 'last_visited_at', $visitDate]];
            //schema
            $reviewSchema =  "lelaibd://reviewStore/list?cityId={$city}&contractorId={$contractor_id}";
            $noOrderSchema =  "lelaibd://customerStore/list?cityId={$city}&neverOrder=1&listType=1&contractorId={$contractor_id}";
            $noActiveSchema = "lelaibd://customerStore/list?cityId={$city}&latelyOrderDay=30&minOrder=0&maxOrder=4&listType=1&contractorId={$contractor_id}";
            $noVisitCustomerSchema = "lelaibd://customerStore/list?cityId={$city}&latelyVisitDay=15&contractorId={$contractor_id}";
            $allCustomerSchema = "lelaibd://customerStore/list?cityId={$city}&contractorId={$contractor_id}";
        } else{
            //注册未审核
            $reviewConditions = ['city' => $city, 'status' => 0];
            //注册未下单
            $noOrderConditions = ['city' => $city, 'status' => 1, 'first_order_id' => 0];
            //15天内没有拜访
            $visitDate = date('Y-m-d', strtotime('-15 days'));
            $noVisitConditions = ['and',['city' => $city, 'status' => 1,],['<', 'last_visited_at', $visitDate]];

            //schema
            $reviewSchema =  "lelaibd://reviewStore/list?cityId={$city}";
            $noOrderSchema =  "lelaibd://customerStore/list?cityId={$city}&neverOrder=1&listType=1";
            $noActiveSchema = "lelaibd://customerStore/list?cityId={$city}&latelyOrderDay=30&minOrder=0&maxOrder=4&listType=1";
            $noVisitCustomerSchema = "lelaibd://customerStore/list?cityId={$city}&latelyVisitDay=15";
            $allCustomerSchema = "lelaibd://customerStore/list?cityId={$city}";

        }
        
        $reviewCustomerCount = LeCustomers::find()->where($reviewConditions)->andWhere(['tag_id' => 1])->count();
        $noOrderCustomerCount = LeCustomers::find()->where($noOrderConditions)->andWhere(['tag_id' => 1])->count();
        $noVisitCustomerCount = LeCustomers::find()->where($noVisitConditions)->andWhere(['tag_id' => 1])->count();
        $responseData = [
            'quick_entry' => [
                [
                    'name' => '注册审核',
                    'number' => $reviewCustomerCount,
                    'schema' => $reviewSchema,
                ],
                [
                    'name' => '注册未下单用户',
                    'number' => $noOrderCustomerCount,
                    'schema' => $noOrderSchema
                ],
                [
                    'name' => '不活跃用户',
                    'sub_name' => '30天内少于4单',
                    'schema' => $noActiveSchema
                ],
                [
                    'name' => '亟待拜访用户',
                    'number' => $noVisitCustomerCount,
                    'sub_name' => '15天内未拜访用户',
                    'schema' =>$noVisitCustomerSchema
                ],
                [
                    'name' => '查看全部超市',
                    'schema' => $allCustomerSchema
                ],
            ]
        ];
        $response->setFrom(Tools::pb_array_filter($responseData));
        return $response;
    }

    public static function request()
    {
        return new ContractorAuthenticationRequest();
    }

    public static function response()
    {
        return new ManageResponse();
    }
}