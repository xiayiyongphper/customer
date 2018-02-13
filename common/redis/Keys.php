<?php
namespace common\redis;
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 16-6-30
 * Time: 上午11:33
 */
class Keys
{
    /**
     * 获取每日限购的ｋｅｙ
     * @param $customerId
     * @param $city
     * @return string
     */
    public static function getDailyPurchaseHistory($customerId, $city)
    {
        return 'daily_purchase_history_' . $customerId . '_' . $city;
    }

    public static function getRedisESQueueKey()
    {
        return ENV_SYS_NAME . '_es_queue';
    }
}