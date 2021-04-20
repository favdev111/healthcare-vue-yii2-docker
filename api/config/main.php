<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [],
    'controllerNamespace' => 'api\controllers',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'geocoding' => [
            'class' => 'common\components\Geocoding',
        ],
        'user' => [
            'class' => 'modules\account\components\Account',
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
                    'logFile' => '@log/api/app.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/yii-queue.log',
                    'categories' => ['yii-queue'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/mainPlatformPayments.log',
                    'categories' => ['platform-payments'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/lead.log',
                    'categories' => ['lead'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/sms.log',
                    'categories' => ['sms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/charge.log',
                    'categories' => ['charge'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/incoming-sms.log',
                    'categories' => ['incoming-sms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/leadSms.log',
                    'categories' => ['leadSms'],
                ],                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/failedPayouts.log',
                    'categories' => ['failedPayouts'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/terms.log',
                    'categories' => ['terms'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/api/payment.log',
                    'categories' => ['payment'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
                    'logFile' => '@log/api/post-payment.log',
                    'categories' => ['post-payment'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/notification.log',
                    'categories' => ['notification'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/mail.log',
                    'categories' => ['mail'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/chat.log',
                    'categories' => ['chat'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/ipinfo.log',
                    'categories' => ['ipinfo'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@log/api/push.log',
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
            'baseUrl' => '/api',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'baseUrl' => '/api',
            'rules' => [
                'GET constants' => 'account/default/constants',
                'OPTIONS constants' => 'account/default/options',
                'GET config' => 'account/default/config',
                'OPTIONS config' => 'account/default/options',
                'GET payment/token/account' => 'account/default/account-by-payment-token',
                'OPTIONS payment/token/account' => 'account/default/options',
                'POST payment/token/card' => 'payment/card-info/create-card-by-token',
                'OPTIONS payment/token/card' => 'payment/card-info/options',
                'OPTIONS cards/company' => 'payment/card-info/options',
                'GET statistics' => 'analytics/statistic/index',
                'OPTIONS statistics' => 'analytics/statistic/index',
                'GET statistics/kpi' => 'analytics/statistic/kpi',
                'OPTIONS statistics/kpi' => 'analytics/statistic/kpi',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'chats' => 'chat/default',
                    ],
                    'patterns' => [
                        'POST send/<chatUserId:\d+>' => 'send',
                        'OPTIONS send/<chatUserId:\d+>' => 'options',
                        'POST mark-read/<messageId:\w+>/<dialogId:\w+>' => 'mark-read',
                        'OPTIONS mark-read/<messageId:\w+>/<dialogId:\w+>' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'location' => 'account/location',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'employee' => 'account/employee',
                    ],
                    'extraPatterns' => [
                        'GET <id:\d+>/statistic' => 'statistic',
                        'OPTIONS <id:\d+>/statistic' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'employee-clients' => 'account/employee-clients',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'job-apply' => 'account/job-apply',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'account-returns' => 'account/account-returns',
                    ],
                    'extraPatterns' => [
                        'GET statistic' => 'statistic',
                        'OPTIONS statistic' => 'options',
                        'GET statistic-download' => 'download',
                        'OPTIONS statistic-download' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'post-payment' => 'account/post-payment',
                    ],

                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'teams' => 'account/team',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'account-team' => 'account/account-team',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'lessons' => 'account/lesson',
                    ],
                    'except' => [
                        'create',
                        'delete',
                    ],
                    'extraPatterns' => [
                        'GET pdf' => 'pdf',
                        'OPTIONS pdf' => 'options',
                        'GET totals' => 'totals',
                        'OPTIONS totals' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'clients' => 'account/client',
                    ],
                    'extraPatterns' => [
                        'POST <id:\d+>/send-invitation' => 'send-invitation',
                        'OPTIONS <id:\d+>/send-invitation' => 'options',
                        'PUT <id:\d+>/task-list-position' => 'task-list-position',
                        'OPTIONS <id:\d+>/task-list-position' => 'options',
                        'GET download/csv' => 'download-csv',
                        'OPTIONS download/csv' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'prefix' => 'clients/<clientAccountId:\d+>',
                    'controller' => [
                        'notes' => 'account/client-note',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'jobs' => 'account/job',
                    ],
                    'extraPatterns' => [
                        'PUT reopen/<id:\d+>' => 'reopen',
                        'OPTIONS reopen/<id:\d+>' => 'options',
                        'PUT repost/<id:\d+>' => 'repost',
                        'OPTIONS repost/<id:\d+>' => 'options',
                        'GET xml' => 'xml',
                        'OPTIONS xml' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'job-hires' => 'account/job-hire',
                    ],
                    'extraPatterns' => [
                        'GET averages' => 'averages',
                        'OPTIONS averages' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'job-offers' => 'account/job-offer',
                    ],
                    'only' => [
                        'create',
                        'update',
                        'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'cards' => 'payment/card-info',
                    ],
                    'except' => [
                        'update',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'tutors' => 'account/tutor',
                    ],
                    'except' => [
                        'create',
                        'update',
                        'delete',
                    ],
                    'extraPatterns' => [
                        'POST send-job-link' => 'send-job-link',
                        'OPTIONS send-job-link' => 'options',
                        'POST check-notification-about-job' => 'check-notification-about-job',
                        'OPTIONS check-notification-about-job' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'reviews' => 'account/review',
                    ],
                    'only' => [
                        'index',
                        'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'bank-accounts' => 'payment/bank-account',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'payment-bank-accounts' => 'payment/payment-bank-account',
                    ],
                    'except' => [
                        'update',
                    ],
                    'extraPatterns' => [
                        'POST verify/<id:\d+>' => 'verify',
                        'OPTIONS verify/<id:\d+>' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'payment-customers' => 'payment/payment-customer',
                    ],
                    'only' => [
                        'options',
                        'update',
                        'view',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'payment-accounts' => 'payment/payment-account',
                    ],
                    'only' => [
                        'options',
                        'view',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'notifications' => 'notification/notification',
                    ],
                    'only' => [
                        'options',
                        'index',
                        'update',
                        'view',
                        'settings',
                        'settings-update',
                        'mark-as-read-all',
                    ],
                    'extraPatterns' => [
                        'OPTIONS settings' => 'options',
                        'GET settings' => 'settings',
                        'OPTIONS mark-as-read-all' => 'options',
                        'PATCH mark-as-read-all' => 'mark-as-read-all',
                        'POST settings' => 'settings-update',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'transactions' => 'payment/transaction',
                    ],
                    'only' => [
                        'options',
                        'view',
                        'index',
                        're-charge',
                        'refund',
                    ],

                    'extraPatterns' => [
                        'POST re-charge/' => 're-charge',
                        'OPTIONS re-charge/' => 'options',
                        'POST refund/<id:\d+>/' => 'refund',
                        'OPTIONS refund/<id:\d+>/' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'accounts' => 'account/default',
                    ],
                    'patterns' => [
                        'POST login' => 'login',
                        'POST forgot' => 'forgot',
                        'POST reset/<token>' => 'reset',
                        'POST change-password' => 'change-password',
                        'POST signup-company' => 'signup-company',
                        'GET me' => 'me',
                        'POST me' => 'me-update',
                        'OPTIONS signup-company' => 'options',
                        'OPTIONS login' => 'options',
                        'OPTIONS me' => 'options',
                        'OPTIONS change-password' => 'options',
                        'OPTIONS forgot' => 'options',
                        'OPTIONS reset/<token>' => 'options',
                        'POST confirm-email/' => 'confirm-email',
                        'OPTIONS confirm-email/' => 'options',
                        'POST cancel-email/' => 'cancel-email',
                        'OPTIONS cancel-email/' => 'options',
                        'POST invite-employee/' => 'invite-employee',
                        'OPTIONS invite-employee/' => 'options',
                        'POST signup-employee/' => 'signup-employee',
                        'OPTIONS signup-employee/' => 'options',
                        'POST pusher-auth' => 'pusher-auth',
                        'OPTIONS pusher-auth' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => [
                        'auto' => 'account/autocomplete',
                    ],
                    'patterns' => [
                        'GET subjects/<query>' => 'subjects',
                        'OPTIONS subjects/<query>' => 'options',

                        'GET college' => 'college',
                        'GET subjects' => 'subjects-old',
                        'GET subjects-selectize' => 'subjects-selectize',
                        'GET subjects-list' => 'subjects',
                        'GET subjects-with-category-selectize' => 'subjects-selectize',
                        'GET subjects-without-cat' => 'subjects-without-cat',
                        'GET subjects-without-cat-selectize' => 'subjects-selectize',
                        'GET college/<id:\d+>' => 'college-by-id',
                        'GET city-by-zipcode/<zipcode:\d+>' => 'city-by-zipcode',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'client-balance' => 'account/client-balance-transaction',
                    ],
                    'except' => [
                        'update',
                        'delete',
                    ],
                    'extraPatterns' => [
                        'GET <clientId:\d+>/pdf' => 'pdf',
                        'OPTIONS <clientId:\d+>/pdf' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'job-lead' => 'account/job-lead',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'files' => 'account/files',
                    ],
                    'extraPatterns' => [
                        'OPTIONS upload' => 'options',
                        'POST upload' => 'create',
                        'OPTIONS delete/<id:\d+>' => 'options',
                        'DELETE delete/<id:\d+>' => 'delete',
                        'OPTIONS download/<id:\d+>' => 'options',
                        'GET download/<id:\d+>' => 'view',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'labels' => 'labels/label',
                    ],
                    'extraPatterns' => [
                        'OPTIONS categories' => 'options',
                        'GET categories' => 'categories',
                        'OPTIONS <categorySlug:[\w\-\=\_]+>' => 'options',
                        'GET <categorySlug:[\w\-\=\_]+>' => 'list-by-category-id',
                        'OPTIONS assign' => 'options',
                        'POST assign' => 'assign-to-relation',
                        'OPTIONS assign/update/<relationId:\d+>' => 'options',
                        'PUT assign/update/<relationId:\d+>' => 'update-label-relation',
                        'OPTIONS delete/<relationId:\d+>' => 'options',
                        'DELETE delete/<relationId:\d+>' => 'delete-relation',
                    ],
                ],
            ],
        ],
    ],
    'modules' => [
        'account' => [
            'modelClasses' => [
                'Account' => 'modules\account\models\api\Account',
                'AccountClient' => 'modules\account\models\api\AccountClient',
                'Profile' => 'modules\account\models\api\Profile',
                'JobHire' => 'modules\account\models\api\JobHire',
                'JobOffer' => 'modules\account\models\api\JobOffer',
            ],
        ],
        'payment' => [
            'modelClasses' => [
                'BankAccount' => 'modules\payment\models\api\BankAccount',
                'Account' => 'modules\payment\models\api\PaymentAccount',
                'PaymentCustomer' => 'modules\payment\models\api\PaymentCustomer',
                'Transaction' => 'modules\payment\models\api\Transaction',
            ],
        ],
        'labels' => [
            'class' => 'modules\labels\Module',
        ],
    ],
    'params' => $params,
];
