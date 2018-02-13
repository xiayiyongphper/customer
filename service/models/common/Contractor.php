<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/22
 * Time: 14:17
 */

namespace service\models\common;

use common\models\contractor\VisitRecords;
use common\models\ContractorAuthAssignment;
use common\models\ContractorAuthItem;
use common\models\CustomerAuditLog;
use common\models\CustomerLevel;
use common\models\CustomerType;
use common\models\LeContractor;
use common\models\LeCustomers;
use common\models\LeCustomersIntention;
use common\models\Region;
use framework\components\Date;
use framework\components\ToolsAbstract;
use framework\protocolbuffers\Message;
use service\components\Tools;
use service\message\contractor\ContractorResponse;
use service\resources\ResourceAbstract;

abstract class Contractor extends ResourceAbstract
{

    //普通业务员
    const COMMON_CONTRACTOR = '普通业务员';
    //验证码过期时间
    const CODE_EXPIRATION_TIME = 60; //60s

    public $role_permission;
    public $role_item;

    public static function parseRequest($data){
        /** @var Message $request */
        $request = get_called_class()::request();
        $request->parseFromString($data);
        return $request;
    }

    /**
     * getContractorInfo
     * Author Jason Y. wang
     * 业务员信息
     * @param LeContractor $contractor
     * @param $role_permission
     * @return bool|ContractorResponse
     */
    public function getContractorInfo(LeContractor $contractor, $role_permission)
    {
        if ($contractor) {
            $response = new ContractorResponse();
            $city = Region::findOne(['code' => $contractor->city]);
            $city_name = '';
            if ($city) {
                $city_name = $city->chinese_name;
            }
            $city_list = array_filter(explode('|', $contractor->city_list));
            $responseData = array(
                'contractor_id' => $contractor->entity_id,
                'name' => $contractor->name,
                'phone' => $contractor->phone,
                'auth_token' => $contractor->auth_token,
                'city' => $contractor->city,
                'city_name' => $city_name,
                'role' => $contractor->role,
                'role_permission' => $role_permission,
                'city_list' => $city_list,
            );
            $responseData['role_name'] = $this->getRole($contractor->entity_id);
            $city_list = Region::find()->select('chinese_name')->where(['code' => $city_list])->column();
            $responseData['city_list_name'] = implode(';', $city_list);
            //Tools::log(Tools::pb_array_filter($responseData),'wangyang.log');
            $response->setFrom(Tools::pb_array_filter($responseData));
            return $response;

        }
    }

    protected function increase8Hours($date)
    {
        if (is_string($date) && strlen($date) > 0) {
            return date('Y-m-d H:i:s', strtotime($date . ' +8 hours'));
        } else {
            return '';
        }
    }

    /**
     * getStoreInfo
     * Author Jason Y. wang
     * 获取小店详情
     * @param $store_id
     * @param LeContractor $contractor
     * @return array|bool
     */
    public function getStoreInfo($store_id, LeContractor $contractor)
    {
        $store = LeCustomers::findOne(['entity_id' => $store_id]);
        if ($store) {
            $responseData = array(
                'customer_id' => $store->entity_id,
                'phone' => $store->phone,
                'address' => $store->address,
                'detail_address' => $store->detail_address,
                'area_id' => $store->area_id,
                'store_name' => $store->store_name,
                'lat' => $store->lat,
                'lng' => $store->lng,
                'status' => $store->status,
                'storekeeper' => $store->storekeeper,
                'contractor' => $contractor->name,
                'type' => $store->type,
                'level' => $store->level,
                'business_license_no' => $store->business_license_no,
                'business_license_img' => $store->business_license_img,
                'store_front_img' => $store->store_front_img,
            );
            if ($store->type && is_string($store->type)) {
                $type = explode('|', $store->type);
                $responseData['type'] = array_filter($type);
            }
            $data = new Date();
            $created_at = $data->date('Y-m-d H:i:s', $store->created_at);
            $responseData['created_at'] = $created_at;

            $region = Region::findOne(['code' => $store->city]);
            if ($region) {
                $responseData['city_name'] = $region->chinese_name;
            }

            //审核不通过原因
            $customer_audit_log = CustomerAuditLog::find()
                ->where(['customer_id' => $store->entity_id, 'type' => 1])
                ->orderBy(['created_at' => SORT_DESC])->one();
            if ($customer_audit_log) {
                $responseData['not_pass_reason'] = $customer_audit_log['content'];
            } else {
                $responseData['not_pass_reason'] = '';
            }

            return $responseData;
        }
        return null;
    }

    /**
     * getStoreInfo
     * Author Jason Y. wang
     * 获取小店详情
     * @param $store_id
     * @param int $customer_style
     * @return array|bool
     */
    public function getStoreInfoV2($store_id, $customer_style = 0)
    {
        $responseData = [];
        //Tools::log($store_id, 'wangyang.log');
        $store = null;
        if ($customer_style == 0) {
            $store = LeCustomers::findOne(['entity_id' => $store_id]);
            $responseData['customer_style'] = 0;
        } else {
            $store = LeCustomersIntention::findOne(['entity_id' => $store_id]);
            $responseData['customer_style'] = 1;
        }
        if (!$store) {
            ContractorException::contractorSystemError();
        }
        $region = Region::findOne(['code' => $store->city]);
        if ($region) {
            $responseData = array_merge($responseData, [
                'city_name' => $region->chinese_name,
            ]);
        }

        $created_at = $this->increase8Hours($store->created_at);
        $apply_at = $this->increase8Hours($store->apply_at);

        if ($store) {
            $responseData = array_merge($responseData, array(
                'customer_id' => $store->entity_id,
                'phone' => $store->phone,
                'address' => $store->address,
                'detail_address' => $store->detail_address,
                'area_id' => $store->area_id,
                'store_name' => $store->store_name,
                'lat' => $store->lat,
                'lng' => $store->lng,
                'status' => $store->status,
                'storekeeper' => $store->storekeeper,
                'contractor' => $store->contractor,
                'contractor_id' => $store->contractor_id,
                'type' => array_filter(explode('|', $store->type)),
                'level' => $store->level,
                'business_license_no' => $store->business_license_no,
                'business_license_img' => $store->business_license_img,
                'store_front_img' => $store->store_front_img,
                'created_at' => $created_at,
                'apply_at' => $apply_at,
                'username' => $store->username,
                'storekeeper_instore_times' => $store->storekeeper_instore_times,
            ));

            $types = [];
            if ($store->type && is_string($store->type)) {
                $types = explode('|', $store->type);
                $types = array_filter($types);
            }
            $customer_types = CustomerType::find()->where(['in', 'entity_id', $types])->all();
            if ($customer_types) {
                $type_name = '';
                /** @var CustomerType $customer_type */
                foreach ($customer_types as $customer_type) {
                    $type_name .= $customer_type->type . ',';
                }
                //去掉最后的逗号
                $responseData['type_name'] = rtrim($type_name, ',');
            }

            //全部等级
            /** @var CustomerLevel $customer_level */
            $customer_level = CustomerLevel::find()->where(['entity_id' => $store->level])->one();
            if ($customer_level) {
                $responseData['level_name'] = $customer_level->level;
            }
        }

        return $responseData;
    }

    /**
     * initContractor
     * Author Jason Y. wang
     * 权限鉴定
     * @param $data
     * @return LeContractor
     * @throws ContractorException
     * @param $data
     * @return array|null|\framework\db\ActiveRecord
     * @throws ContractorException
     */
    protected function initContractor($data)
    {
        $timeStart = microtime(true);
        if ($data->getContractorId() && $data->getAuthToken()) {
//            $this->role_item = ContractorAuthItem::find()->select('name')->where(['type' => 1])->column();
            /** @var LeContractor $contractor */
            $contractor = LeContractor::find()->where(['entity_id' => $data->getContractorId(), 'auth_token' => $data->getAuthToken()])->one();
            if ($contractor) {
                if ($contractor->status == 0) {
                    ContractorException::contractorDisabled();
                }
                if (!$contractor->city_list) {
                    ContractorException::contractorCityListEmpty();
                }
                $contractor->role = $this->getRole($contractor->entity_id);
                $result = $this->initRole($contractor->entity_id, $contractor->auth_token);
                Tools::log($result, 'contractor_role.log');
                $timeEnd = microtime(true);
                $elapsed = $timeEnd - $timeStart;
                ToolsAbstract::log('contractor_id:' . $data->getContractorId() . ',elapsed:' . $elapsed, 'initContractor.log');
                return $contractor;
            }
        }
        ContractorException::contractorAuthTokenExpired();
    }

    protected function getRole($contractor_id)
    {
        /** @var ContractorAuthAssignment $auth_role */
        $auth_role = ContractorAuthAssignment::find()->where(['user_id' => $contractor_id])->one();
        if ($auth_role) {
            return $auth_role->item_name;
        }
        ContractorException::contractorNotAllocateRole();
    }

    protected function initRole($contractor_id, $auth_token)
    {
        //初始化  获取该业务员权限列表
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ENV_CONTRACTOR_PERMISSION_URL);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "{$contractor_id}:{$auth_token}");
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = explode(',', trim(str_replace("\"", '', $result), '[]'));
        $this->role_permission = $result;
        return $result;
    }

    /**
     * @param VisitRecords $record
     * @return array
     */
    protected function convertVisitRecordArray($record)
    {
        if ($record instanceof VisitRecords) {
            $record = $record->toArray();
        }

        $gallery = [];
        if (strlen(ToolsAbstract::arrayGetString($record, 'gallery')) > 0) {
            $images = explode(';', ToolsAbstract::arrayGetString($record, 'gallery'));
            foreach ($images as $image) {
                $gallery[] = [
                    'src' => $image,
                ];
            }
        }
        return [
            'record_id' => ToolsAbstract::arrayGetInteger($record, 'entity_id'),
            'contractor_id' => ToolsAbstract::arrayGetInteger($record, 'contractor_id'),
            'contractor_name' => ToolsAbstract::arrayGetString($record, 'contractor_name'),
            'customer_id' => ToolsAbstract::arrayGetInteger($record, 'customer_id'),
            'store_name' => ToolsAbstract::arrayGetString($record, 'store_name'),
            'visit_purpose' => ToolsAbstract::arrayGetString($record, 'visit_purpose'),
            'visit_way' => ToolsAbstract::arrayGetString($record, 'visit_way'),
            'visit_content' => ToolsAbstract::arrayGetString($record, 'visit_content'),
            'feedback' => ToolsAbstract::arrayGetString($record, 'feedback'),
            'created_at' => date('m-d', strtotime(ToolsAbstract::arrayGetString($record, 'visited_at'))),
            'visited_at' => date('Y-m-d H:i:s', strtotime(ToolsAbstract::arrayGetString($record, 'visited_at'))),
            'lat' => ToolsAbstract::arrayGetString($record, 'lat'),
            'lng' => ToolsAbstract::arrayGetString($record, 'lng'),
            'locate_address' => ToolsAbstract::arrayGetString($record, 'locate_address'),
            'is_intended' => ToolsAbstract::arrayGetInteger($record, 'is_intended'),
            'gallery' => $gallery,
            'editable' => ToolsAbstract::dateSub(null, ToolsAbstract::arrayGetString($record, 'created_at')) >= 24 * 3600 ? false : true,
            'store_front_img' => ['src' => ToolsAbstract::arrayGetInteger($record, 'is_intended') > 0 ? ToolsAbstract::arrayGetString($record, 'intend_store_front_img') : ToolsAbstract::arrayGetString($record, 'store_front_img')],
        ];
    }
}
