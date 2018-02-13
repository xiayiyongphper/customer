<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 16-10-12
 * Time: 上午11:39
 */

namespace common\models\contractor;

use framework\db\ActiveRecord;

/**
 * This is the model class for table "ContractorMarkPriceHistory".
 *
 * @property integer $entity_id
 * @property integer $contractor_id
 * @property integer $city
 * @property string $contractor_name
 * @property integer $mark_price_product_id
 * @property float $price
 * @property string $created_at
 */

class ContractorMarkPriceHistory extends ActiveRecord
{

    public static function getDb()
    {
        return \Yii::$app->get('mainDb');
    }

    public static function tableName()
    {
        return 'contractor_mark_price_history';
    }



    public static function getPriceById($mark_price_product_id){
        /** @var  ContractorMarkPriceHistory $priceHistory */
        $priceHistory = ContractorMarkPriceHistory::find()->where(['mark_price_product_id' => $mark_price_product_id])
            ->orderBy('created_at desc')->one();
        if($priceHistory){
            return $priceHistory->price;
        }
        return 0;
    }

    public static function getLatMarkPriceInfo($mark_price_product_id){
        /** @var  ContractorMarkPriceHistory $priceHistory */
        $priceHistory = ContractorMarkPriceHistory::find()->where(['mark_price_product_id' => $mark_price_product_id])
            ->orderBy('created_at desc')->one();
        return $priceHistory;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(MarkPriceProduct::className(), ['entity_id' => 'mark_price_product_id']);
    }

}