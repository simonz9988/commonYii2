<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'robot',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'controllerNamespace' => 'sunny\controllers',
    'timeZone' => 'Asia/Shanghai',
    'bootstrap' => ['log','gii'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'sunnyGCw78PsgKB5ApPTYw8fexUweeKTq2ASDFGHJKWERTYU',
            "enableCsrfValidation"=>false,
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-bit', 'httpOnly' => true],
        ],
        // 路由配置
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules' => [
                '<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            ],
        ],
        'EcoErrCode' => ['class' => 'sunny\components\EcoErrCode'],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','trace'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        //官网错误方法类
        'EcoErrcode' => ['class' => 'common\components\EcoErrCode'],
        //官网获取session相关方法
        'Ecosession' =>  ['class' => 'common\components\Ecosession'],
        //校验类
        'Filter' => ['class' => 'common\components\Filter'],
        'MyRedis' => ['class' => 'common\components\MyRedis'],

    ],
    'params' => $params,

    'language' => 'zh-CN',

];
