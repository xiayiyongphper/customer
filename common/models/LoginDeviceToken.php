<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/4/26
 * Time: 14:52
 */
namespace common\models;

use framework\components\ToolsAbstract;
use Yii;
use framework\db\ActiveRecord;

/**
 * This is the model class for table "device_token_login".
 *
 * @property integer $entity_id
 * @property integer $customer_id
 * @property string $token
 * @property string $created_at
 * @property string $updated_at
 * @property integer $is_del
 */
class LoginDeviceToken extends ActiveRecord
{
    /**
     * 未删除
     */
    const NOT_DELETE = 0;
    /**
     * 已删除
     */
    const DELETED = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'device_token_login';
    }

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
    public function rules()
    {
        return [
            [['customer_id', 'token'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $datetime = ToolsAbstract::getDate()->date();
        if ($insert) {
            $this->created_at = $datetime;
        }

        $this->updated_at = $datetime;
        return parent::beforeSave($insert);
    }
}
