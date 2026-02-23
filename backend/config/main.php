<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),

    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'timeZone' => 'Asia/Shanghai',
    //'bootstrap' => ['log','cas'],//
    'bootstrap' => ['log'],// 不启用cas
    'modules' => [
        'cas' => [
            'class' => 'alcad\cas\Cas',
        ],
        'gii' => [
            'class' => 'yii\gii\Module',
            //自定义允许访问gii模块的ip或者ip段
            'allowedIPs' => ['127.0.0.1', '::1','172.17.0.2','172.17.0.1','10.88.98.31']
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => '3X3GCw78PsgKB5ApPTYw8fexUweeKTq2',
            "enableCsrfValidation"=>false,
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'casUser' => ['class' => 'alcad\cas\CasUser'],
        'EcoErrCode' => ['class' => 'backend\components\EcoErrCode'],
        // 路由配置
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules' => [
                '<controller:\w+>/<id:\d+>'              => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,

    'language' => 'zh-CN',
];
