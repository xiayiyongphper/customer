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
use service\message\contractor\ContractorResponse;
use service\message\contractor\ManageResponse2;
use service\models\common\Contractor;
use service\models\common\Customer;
use service\models\common\CustomerException;

/**
 * Author Jason Y.Wang
 * Class customerManageEntry2
 * @package service\resources\customers\v1
 * 业务员1.6版本新增
 */
class customerManageEntry2 extends Customer
{
    const CLASSIFY_IMPORTANT_VISIT = 2; //重点拜访用户
    const CLASSIFY_ACTIVE_RISE_BEGIN_MONTH = 3; //月初活跃上升用户
    const CLASSIFY_CONTINUE_ACTIVE = 4; //持续活跃用户
    const CLASSIFY_SILENT_BEGIN_MONTH_NOT_VISIT = 5; //月初为沉睡用户30天内无拜访
    const CLASSIFY_SILENT_BEGIN_MONTH_VISITED = 6; //30天内有拜访，月初仍未沉睡用户
    const CLASSIFY_ACTIVE = 7;  //活跃超市
    const CLASSIFY_FOCUS = 8;  //重点关注超市
    const CLASSIFY_SILENT = 9;  //沉默超市
    const CLASSIFY_ALL = 10;  //全部超市
    const CLASSIFY_MONTH_ORDERED = 11;  //本月已下单
    const CLASSIFY_MONTH_NONE_ORDERED = 12; //本月未下单

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

        //超市类型管理
        $customer_visit_manage = $this->getCustomerVisitManage($city, $contractor);
        $customer_type_manage = $this->getCustomerTypeManage($city, $contractor);
        $customer_order_stat = $this->getCustomerOrderStat($city, $contractor);
        $customer_review_stat = $this->getCustomerReviewStat($city);

        $responseData = [
            'quick_entry_block' => [
                [
                    'name' => '月度拜访管理',
                    'quick_entry' => $customer_visit_manage
                ],
                [
                    'name' => '超市实时类型',
                    'quick_entry' => $customer_type_manage
                ],
                [
                    'name' => '超市下单情况',
                    'quick_entry' => $customer_order_stat
                ],
                [
                    'name' => '超市审核管理',
                    'quick_entry' => $customer_review_stat
                ],

            ]
        ];
        $response->setFrom(Tools::pb_array_filter($responseData));
        return $response;
    }

    /**
     * Author Jason Y.Wang
     * @param $city
     * @param ContractorResponse $contractor
     * @return array
     * 拜访用户管理
     */
    public function getCustomerVisitManage($city, $contractor)
    {

        $important_visit = "lelaibd://customerStore/list?cityId={$city}&customerRange=" . self::CLASSIFY_IMPORTANT_VISIT;
        $active_rise_begin_month = "lelaibd://customerStore/list?cityId={$city}&customerRange=" . self::CLASSIFY_ACTIVE_RISE_BEGIN_MONTH;
        $continue_active = "lelaibd://customerStore/list?cityId={$city}&customerRange=" . self::CLASSIFY_CONTINUE_ACTIVE;
        $silent_begin_month_not_visit = "lelaibd://customerStore/list?cityId={$city}&customerRange=" . self::CLASSIFY_SILENT_BEGIN_MONTH_NOT_VISIT;
        $silent_begin_month_visit = "lelaibd://customerStore/list?cityId={$city}&customerRange=" . self::CLASSIFY_SILENT_BEGIN_MONTH_VISITED;


        if ($contractor->getRole() == Contractor::COMMON_CONTRACTOR) {
            $important_visit = $important_visit . "&contractorId={$contractor->getContractorId()}";
            $active_rise_begin_month = $active_rise_begin_month . "&contractorId={$contractor->getContractorId()}";
            $continue_active = $continue_active . "&contractorId={$contractor->getContractorId()}";
            $silent_begin_month_not_visit = $silent_begin_month_not_visit . "&contractorId={$contractor->getContractorId()}";
            $silent_begin_month_visit = $silent_begin_month_visit . "&contractorId={$contractor->getContractorId()}";
        }

        $data = [
            [
                'name' => '本月重点关注门店',
                'schema' => $important_visit,
                'sub_name' => '拜访优先级1级'
            ],
            [
                'name' => '本月活跃上升门店',
                'schema' => $active_rise_begin_month,
                'sub_name' => '拜访优先级2级'

            ],
            [
                'name' => '持续活跃用户',
                'schema' => $continue_active,
                'sub_name' => '拜访优先级3级'
            ],
            [
                'name' => '有希望唤醒用户',
                'schema' => $silent_begin_month_not_visit,
                'sub_name' => '拜访优先级4级'
            ],
            [
                'name' => '无需拜访用户',
                'schema' => $silent_begin_month_visit,
                'sub_name' => '本月无需拜访'
            ],
        ];

        $date = date('Y-m-d H:i:s');
        $task = LeCustomers::find()->alias('c')
            ->leftJoin('lelai_slim_customer.contractor_visit_task as t', 'c.entity_id = t.customer_id')
            ->andWhere(['<=', 'start_time', $date])->andWhere(['>', 'end_time', $date])
            ->andWhere(['t.status' => 1])
            ->groupBy('visit_task_type');

        if ($contractor->getRole() == Contractor::COMMON_CONTRACTOR) {
            $task = $task->andWhere(['c.contractor_id' => $contractor->getContractorId()]);
            $task_schema = "lelaibd://customerStore/visitTaskList?cityId={$city}&contractorId={$contractor->getContractorId()}";
        } else {
            $task = $task->andWhere(['c.city' => $city]);
            $task_schema = "lelaibd://customerStore/visitTaskList?cityId={$city}";
        }

        $task_count = $task->count();
        if ($task_count > 0) {
            $task_entry = [
                'name' => '定向拜访任务列表',
                'schema' => $task_schema,
                'sub_name' => '包含' . $task_count . '个任务'
            ];
            array_unshift($data, $task_entry);
        }

        return $data;
    }

    /**
     * Author Jason Y.Wang
     * @param $city
     * @param ContractorResponse $contractor
     * @return array
     * 超市类型管理
     */
    public function getCustomerTypeManage($city, $contractor)
    {
        $activeSchema = "lelaibd://customerStore/list?cityId={$city}&classify=" . self::CLASSIFY_ACTIVE;
        $focusSchema = "lelaibd://customerStore/list?cityId={$city}&classify=" . self::CLASSIFY_FOCUS;
        $silentSchema = "lelaibd://customerStore/list?cityId={$city}&classify=" . self::CLASSIFY_SILENT;
        $allSchema = "lelaibd://customerStore/list?cityId={$city}";


        if ($contractor->getRole() == Contractor::COMMON_CONTRACTOR) {
            $activeSchema = $activeSchema . "&contractorId={$contractor->getContractorId()}";
            $focusSchema = $focusSchema . "&contractorId={$contractor->getContractorId()}";
            $silentSchema = $silentSchema . "&contractorId={$contractor->getContractorId()}";
            $allSchema = $allSchema . "&contractorId={$contractor->getContractorId()}";
        }

        $data = [
            [
                'name' => '活跃用户',
                'schema' => $activeSchema,
            ],
            [
                'name' => '重点关注用户',
                'schema' => $focusSchema
            ],
            [
                'name' => '沉睡用户',
                'schema' => $silentSchema
            ],
            [
                'name' => '全部超市',
                'schema' => $allSchema
            ],
        ];
        return $data;
    }

    /**
     * Author Jason Y.Wang
     * @param $city
     * @param ContractorResponse $contractor
     * @return array
     * 超市下单情况
     */
    public function getCustomerOrderStat($city, $contractor)
    {
        $thisMonth = Tools::getDate()->date('Y-m-01');
        $monthOrderedSchema = "lelaibd://customerStore/list?cityId={$city}&customerRange=" . self::CLASSIFY_MONTH_ORDERED;
        $monthNoneOrderedSchema = "lelaibd://customerStore/list?cityId={$city}&customerRange=" . self::CLASSIFY_MONTH_NONE_ORDERED;
        $noneOrderedAllSchema = "lelaibd://customerStore/list?cityId={$city}&neverOrder=1&listType=1";


        $monthOrdered = LeCustomers::find()->where(['city' => $city, 'status' => 1, 'tag_id' => 1, 'disabled' => 0])
            ->andWhere(['>=', 'last_place_order_at', $thisMonth]);
        $monthNoneOrdered = LeCustomers::find()->where(['city' => $city, 'status' => 1, 'tag_id' => 1, 'disabled' => 0])
            ->andWhere(['<', 'last_place_order_at', $thisMonth]);
        $noOrderCustomer = LeCustomers::find()->where(['city' => $city, 'status' => 1, 'first_order_id' => 0,
            'tag_id' => 1, 'disabled' => 0]);

        if ($contractor->getRole() == Contractor::COMMON_CONTRACTOR) {
            $monthOrderedSchema = $monthOrderedSchema . "&contractorId={$contractor->getContractorId()}";
            $monthNoneOrderedSchema = $monthNoneOrderedSchema . "&contractorId={$contractor->getContractorId()}";
            $monthOrdered = $monthOrdered->andWhere(['contractor_id' => $contractor->getContractorId()]);
            $monthNoneOrdered = $monthNoneOrdered->andWhere(['contractor_id' => $contractor->getContractorId()]);
            $noOrderCustomer = $noOrderCustomer->andWhere(['contractor_id' => $contractor->getContractorId()]);
        }

        $data = [
            [
                'name' => '本月已下单',
                'schema' => $monthOrderedSchema,
                'number' => $monthOrdered->count(),
            ],
            [
                'name' => '本月未下单',
                'schema' => $monthNoneOrderedSchema,
                'number' => $monthNoneOrdered->count(),
            ],
            [
                'name' => '注册未下单',
                'schema' => $noneOrderedAllSchema,
                'number' => $noOrderCustomer->count(),
            ],

        ];
        return $data;
    }

    /**
     * Author Jason Y.Wang
     * @param $city
     * @return array
     * 超市审核管理
     */
    public function getCustomerReviewStat($city)
    {
        $reviewSchema = "lelaibd://reviewStore/list?cityId={$city}&listType=3";
        $notPassByContractorSchema = "lelaibd://reviewStore/list?cityId={$city}&listType=4";
        $notPassByOfficeSchema = "lelaibd://reviewStore/list?cityId={$city}&listType=5";

        $reviewNum = LeCustomers::find()->where(['city' => $city, 'status' => 0, 'tag_id' => 1])->count();
        $notPassByContractorNum = LeCustomers::find()->where(['city' => $city, 'status' => 2, 'tag_id' => 1, 'disabled' => 0])->count();
        $notPassByOfficeNum = LeCustomers::find()->where(['city' => $city, 'state' => 3, 'tag_id' => 1, 'disabled' => 0])->count();

        $data = [
            [
                'name' => '待审核用户',
                'schema' => $reviewSchema,
                'number' => $reviewNum,
            ],
            [
                'name' => '业务员审核不通过',
                'schema' => $notPassByContractorSchema,
                'number' => $notPassByContractorNum,
            ],
            [
                'name' => '客服审核不通过',
                'schema' => $notPassByOfficeSchema,
                'number' => $notPassByOfficeNum,
            ],

        ];
        return $data;
    }

    public static function request()
    {
        return new ContractorAuthenticationRequest();
    }

    public static function response()
    {
        return new ManageResponse2();
    }
}