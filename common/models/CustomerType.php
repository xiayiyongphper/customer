<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "region_area".
 *
 * @property integer $entity_id
 * @property string $type
 */
class CustomerType extends \framework\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_type';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('mainDb');
    }
}
