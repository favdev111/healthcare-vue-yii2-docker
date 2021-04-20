<?php

use common\components\app\Environment;

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'settings' => [
            'class' => \modules\core\components\Settings::class,
            'cache' => 'cache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning', 'info'],
                    'logFile' => '@log/console/queue.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/automatch.log',
                    'categories' => ['automatch'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/charge.log',
                    'categories' => ['charge'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/inactiveAccountEmails.log',
                    'categories' => ['inactiveAccountEmails'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'logFile' => '@log/console/queue-error.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@log/console/app.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@log/console/jobs.log',
                    'categories' => ['job'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/yii-queue.log',
                    'categories' => ['yii-queue'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/mainPlatformPayments.log',
                    'categories' => ['platform-payments'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/lead.log',
                    'categories' => ['lead'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/sms.log',
                    'categories' => ['sms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/incoming-sms.log',
                    'categories' => ['incoming-sms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/leadSms.log',
                    'categories' => ['leadSms'],
                ],                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/failedPayouts.log',
                    'categories' => ['failedPayouts'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/terms.log',
                    'categories' => ['terms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/console/payment.log',
                    'categories' => ['payment'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/console/post-payment.log',
                    'categories' => ['post-payment'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/notification.log',
                    'categories' => ['notification'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/setDebit.log',
                    'categories' => ['setDebit'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/mail.log',
                    'categories' => ['mail'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/mail-error.log',
                    'categories' => ['mail-error'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/chat.log',
                    'categories' => ['chat'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/ipinfo.log',
                    'categories' => ['ipinfo'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/console/push.log',
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
        'cache' => [
            'keyPrefix' => 'console_',
        ],
        'transferHandlerService' => [
            'class' => \modules\payment\components\TransferHandlerService::class,

        ],
        'cacheBackend' => [
            'class' => 'yii\caching\DummyCache',
            'keyPrefix' => 'backend_',
        ],
        'urlManager' => [
            'baseUrl' => \common\helpers\Url::getFrontendUrl(),
            'rules' => [],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'user' => [
            'class' => 'modules\account\components\Account',
            'enableSession' => false,
        ],
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
        'migrate' => [
            'class' => \console\components\controllers\MigrateController::class,
            'migrationLookup' => [
                '@console/migrations',
                '@modules/seo/migrations',
            ],
        ],
    ],
    'params' => $params,
];
