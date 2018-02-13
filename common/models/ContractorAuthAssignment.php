<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $user_id
 * @property string $item_name
 */
class ContractorAuthAssignment extends \framework\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_assignment';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('mainDb');
    }
}
