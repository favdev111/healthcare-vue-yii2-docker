<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'backend',
    'homeUrl' => env('BACKEND_URL'),
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'container' => [
        'definitions' => [
            'yii\grid\GridView' => [
                'class' => 'yii\grid\GridView',
                'pager' => [
                    'linkContainerOptions' => [
                        'class' => 'page-item',
                    ],
                    'linkOptions' => [
                        'class' => 'page-link',
                    ]
                ],
            ],
            'backend\components\widgets\googlePlace\GooglePlaceAsset' => [
                'class' => 'backend\components\widgets\googlePlace\GooglePlaceAsset',
                'googleParams' => [
                    'libraries' => 'places',
                    'sensor' => true,
                    'language' => 'en-US',
                    'key' => env('GOOGLE_MAPS_API_KEY'),
                ],
            ],
        ],
    ],
    'modules' => [
        'account' => [
            'modelClasses' => [
                'Account' => \modules\account\models\backend\Account::class,
                'AccountClient' => \modules\account\models\backend\AccountClient::class,
            ],
        ],
        'payment' => [
            'modelClasses' => [
                'Transaction' => 'modules\payment\models\Transaction',
            ],
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module',
        ],
        'labels' => [
            'class' => 'modules\labels\Module',
        ],
    ],
    'components' => [
        'formatter' => [
            'nullDisplay' => '<span class="text-danger">(not set)</span>',
        ],
        'request' => [
            'class' => 'backend\components\web\Request',
            'baseUrl' => '/backend',
            'csrfParam' => '_csrf-backend',
        ],
        'settings' => [
            'class' => \modules\core\components\Settings::class,
            'cache' => 'cache',
        ],
        'urlManager' => require(__DIR__ . '/url-manager.php'),
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'assetManager' => [
            'linkAssets' => true,
            'bundles' => [
                'dosamigos\selectize\SelectizeAsset' => false,
                'yii\bootstrap\BootstrapAsset' => false,
                'yii\bootstrap\BootstrapPluginAsset' => false,
                'kartik\select2\ThemeDefaultAsset' => false,
            ],
        ],
        'user' => [
            'class' => 'backend\components\Account',
            'enableAutoLogin' => true,
            'identityClass' => 'backend\models\Account',
            'identityCookie' => [
                'name' => '_identity-backend',
                'httpOnly' => true,
            ],
            'loginUrl' => ['/site/login'],
        ],
        'session' => [
            'name' => 'backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/backend/app.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/yii-queue.log',
                    'categories' => ['yii-queue'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/mainPlatformPayments.log',
                    'categories' => ['platform-payments'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/lead.log',
                    'categories' => ['lead'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/sms.log',
                    'categories' => ['sms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/incoming-sms.log',
                    'categories' => ['incoming-sms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/leadSms.log',
                    'categories' => ['leadSms'],
                ], [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/failedPayouts.log',
                    'categories' => ['failedPayouts'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/terms.log',
                    'categories' => ['terms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/backend/payment.log',
                    'categories' => ['payment'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/backend/post-payment.log',
                    'categories' => ['post-payment'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/notification.log',
                    'categories' => ['notification'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/mail.log',
                    'categories' => ['mail'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/chat.log',
                    'categories' => ['chat'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/ipinfo.log',
                    'categories' => ['ipinfo'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/backend/push.log',
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
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'cache' => [
            'keyPrefix' => 'backend_',
        ],
        'cacheConsole' => [
            'class' => 'yii\caching\DummyCache',
            'keyPrefix' => 'console_',
        ],
        'view' => [
            'theme' => [
                'class' => 'backend\components\Theme',
                'baseUrl' => '/backend/themes/basic',
                'basePath' => '@web/themes/basic',
                'pathMap' => [
                    '@backend/views' => '@themes/basic/backend/views',
                    '@modules' => '@themes/basic/modules',
                ],
            ],
            'on beforeRender' => function () {
                \themes\basic\backend\bundles\FontawesomeAsset::register(Yii::$app->view);
            },
            'on beginPage' => function () {
                \backend\assets\AppAsset::register(Yii::$app->view);
            },
        ],
    ],
    'params' => $params,
];
