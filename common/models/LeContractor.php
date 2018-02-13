<?php
namespace common\models;

use service\components\Tools;
use Yii;
use framework\db\ActiveRecord;

/**
 * User model
 * @property integer $entity_id
 * @property string $auth_token
 * @property string $name
 * @property string $phone
 * @property string $city
 * @property integer $is_admin
 * @property integer $status
 * @property string $city_list
 *
 */
class LeContractor extends ActiveRecord
{

    const CONTRACTOR_INFO_COLLECTION = 'contractor_info_collection';

    public $role;
    public $permission;

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('mainDb');
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contractor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
        ];
    }
    
    public static function findByPhone($phone)
    {
        $contractor = static::findOne(['phone' => $phone]);
        //Tools::log($contractor,'wangyang.log');
        return $contractor;
    }

    public static function findByContractorId($contractor_id)
    {
        $contractor = static::findOne(['entity_id' => $contractor_id]);
        //Tools::log($contractor,'wangyang.log');
        return $contractor;
    }

    public static function getContractorCustomerIds($contractor_id)
    {
        $customerIds = LeCustomers::find()->where(['contractor_id' => $contractor_id])->column();
        $customerIntentionIds = LeCustomersIntention::find()->where(['contractor_id' => $contractor_id])->column();
        return array_merge($customerIds,$customerIntentionIds);
    }

    public static function getVisitedCustomerIds($contractor_id)
    {
        $customerIds = LeCustomers::find()->where(['contractor_id' => $contractor_id])->column();
        $customerIntentionIds = LeCustomersIntention::find()->where(['contractor_id' => $contractor_id])->column();
        return [
            $customerIds,
            $customerIntentionIds,
        ];
    }
    
}
