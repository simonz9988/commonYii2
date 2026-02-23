<?php
return [
    'timeZone' => 'Asia/Shanghai',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
                'CommonLogger' => ['class' => 'common\components\CommonLogger'],
                'SwapApi' => ['class' => 'common\components\SwapApi']
    ],
];
