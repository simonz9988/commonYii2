<?php
$config = [
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' =>1,
        ],
        'session'=>[
            'class'=>'yii\redis\Session',
            'keyPrefix'=>'shopsunny',
            'cookieParams' => [
                'lifetime'=>86400*7,
                'path' => '/',
                'domain' => BASE_DOMAIN,
            ],
            'redis' => [
                'class' => 'yii\redis\Connection',
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 1,
            ],
        ],
        'cache' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 1,
            //'useMemcache1'=>false,
        ],


    ],
];

if (!YII_ENV_TEST) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
