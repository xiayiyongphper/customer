<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/29
 * Time: 15:45
 */
return [
    'events' => [

        //没找到，可能在运营后台或商家后台
        'order_add_comment' => [
            'push' => [
                'class' => 'service\models\customer\Observer',
                'method' => 'orderAddComment',
            ],
        ],
        //没找到，可能在运营后台或商家后台
        'push_notification' => [
            'push' => [
                'class' => 'service\models\customer\Observer',
                'method' => 'pushNotification',
            ],
        ],

        'charge_additional_package' => [
            // 运营后台导入操作充值额度包
            'charge_additional_package' => [
                'class' => 'service\models\customer\Observer',
                'method' => 'charge_additional_package',
            ],
        ],
        'charge_balance' => [
            // 运营后台导入操作充值钱包
            'charge_balance' => [
                'class' => 'service\models\customer\Observer',
                'method' => 'charge_balance',
            ],
        ],
        'register' => [
            // 运营后台审核通过用户
            'approved' => [
                'class' => 'service\models\customer\Observer',
                'method' => 'customerCreated',
            ],
        ],
        'coupon_expire' => [
            //优惠券即将过期
            'expire_push' => [
                'class' => 'service\models\customer\Observer',
                'method' => 'couponExpire',
            ],
        ],
        //core 也发了这个信息
        'coupon_new' => [
            //系统赠送优惠券
            'expire_push' => [
                'class' => 'service\models\customer\Observer',
                'method' => 'couponNew',
            ],
        ]
    ],
];