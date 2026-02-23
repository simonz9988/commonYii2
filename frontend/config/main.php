<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',

    'components' => [
        'request' => [
            'csrfParam' => '_csrf-front',
            'cookieValidationKey' => '3X3GCw78PsgKB5ApPTYw8fexUweeKTq3',
            "enableCsrfValidation"=>false,
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-front', 'httpOnly' => true],
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
];
