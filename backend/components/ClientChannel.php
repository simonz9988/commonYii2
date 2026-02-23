<?php
/**
 * 商城各功能模块涉及渠道相关
 * User: dean.zhang
 * Date: 2018/8/28
 * Time: 13:36
 */
namespace backend\components;

class ClientChannel
{
    //命名方式 功能模块+channel
    //支付方式应用渠道
    public $payment_channel = [
        'PC' => 'PC',
        'WAP' => 'WAP',
        'APP' => 'APP',
        'GLOBALAPP' => 'Global App',
        'WEIXIN' => '微信WAP',
        'WEIXINMINI' => '微信小程序',
        'GLOBALAPPNATIVE' => 'Global App 原生'
    ];

}