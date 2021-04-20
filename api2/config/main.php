<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'api2',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['apiLogRequests'],
    'controllerNamespace' => 'api2\controllers',
    'components' => [
        'user' => [
            'class' => 'modules\account\components\Account',
            'identityClass' => 'modules\account\models\api2\Account',
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => null,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/api2/app.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api2/lead.log',
                    'categories' => ['lead'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api2/push.log',
                    'categories' => ['push'],
                ],
//                [
//                    'class' => \common\components\log\sentry\SentryTarget::class,
//                    'enabled' => env('SENTRY_DSN'),
//                    'dsn' => env('SENTRY_DSN'),
//                    'levels' => ['error', 'warning'],
//                    'clientOptions' => [
//                        'environment' => YII_ENV,
//                    ],
//                    'except' => [
//                        'yii\web\HttpException:404',
//                        'yii\web\HttpException:400',
//                        'yii\web\HttpException:403',
//                    ],
//                    'extraCallback' => function ($message, $extra) {
//                        $user = \Yii::$app->user;
//                        $extra['isGuest'] = $user->isGuest;
//                        $extra['userId'] = $user->id;
//                        return $extra;
//                    },
//                ],
            ],
        ],
        'request' => [
            'class' => \api2\components\Request::class,
            'baseUrl' => '/api/v1',
            'enableCookieValidation' => false,
            'enableCsrfCookie' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => \api2\components\JsonResponseFormatter::class,
                    'prettyPrint' => YII_DEBUG,
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => require(__DIR__ . '/url-manager.php'),
        'apiLogRequests' => [
            'class' => \modules\core\components\ApiLogRequests::class,
            'enable' => true,
        ],
    ],
    'modules' => [
        'account' => [
            'modelClasses' => [
                'Account' => 'modules\account\models\api2\Account',
                'AccountWithoutRestrictions' => 'modules\account\models\api2\AccountWithDeleted',
                'Profile' => 'modules\account\models\api2\Profile',
            ],
        ],
    ],
    'params' => $params,
];
