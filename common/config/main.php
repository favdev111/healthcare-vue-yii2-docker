<?php

use common\components\SalesforceLeadService;
use aksafan\fcm\source\components\Fcm;
use League\Flysystem\AdapterInterface;
use modules\account\models\Role;

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@node_modules' => '@themes/basic/node_modules',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'name' => 'Winitclinic',
    'timeZone' => 'UTC',
    'language' => 'en',
    'bootstrap' => [
        'core',
        'account',
        'chat',
        'log',
        'yiiQueue',
        'yiiQueueHistory',
    ],
    'components' => [
        'notifier' => [
            'class' => '\tuyakhov\notifications\Notifier',
            'channels' => [
                'mail' => [
                    'class' => \modules\notification\channels\mail\Channel::class,
                ],
                'sms' => [
                    'class' => '\tuyakhov\notifications\channels\TwilioChannel',
                    'accountSid' => env('TWILLIO_SID'),
                    'authToken' => env('TWILLIO_TOKEN'),
                    'from' => env('TWILLIO_NUMBER')
                ],
                'pusher' => [
                    'class' => 'modules\notification\channels\pusher\Channel',
                    'channel' => 'notification-{accountId}',
                ],
                'database' => [
                    'class' => '\tuyakhov\notifications\channels\ActiveRecordChannel',
                ],
            ],
        ],
        'settings' => [
            'class' => \modules\core\components\Settings::class,
        ],
        'i18n' => [
            'translations' => [
                'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@common/messages'
                ],
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_NAME'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'on afterOpen' => function ($event) {
                $event->sender->createCommand("SET time_zone = '+00:00'")->execute();
            },
        ],
        'stripePlatformAccount' => [
            'class' => \common\components\StripePlatformAccount::class,
        ],
        'registration' => [
            'class' => \modules\account\components\RegistrationService::class,
        ],
        'elasticsearch' => [
            'class' => 'yii\elasticsearch\Connection',
            'autodetectCluster' => false,
            'nodes' => [],
        ],
        'phoneNumber' => [
            'class' => \common\components\PhoneNumberService::class,
            'defaultPhoneNumber' => env('LOCATION_DEFAULT_PHONE_NUMBER'),
            'defaultTutorPhoneNumber' => env('LOCATION_DEFAULT_TUTOR_PHONE_NUMBER'),
            'callUsPhoneNumber' => env('CALL_US_BUTTON_PHONE_NUMBER'),
        ],
        'phoneRequiredBlocker' => [
            'class' => \common\components\PhoneRequiredBlocker::class,
        ],
        'analytic' => [
            'class' => 'modules\analytics\components\AnalyticsComponent',
        ],
        'queue' => [
            'class' => 'UrbanIndo\Yii2\Queue\Queues\DbQueue',
            'db' => 'db',
            'tableName' => 'queue',
            'waitSecondsIfNoQueue' => 10,
            'module' => 'task',
            'releaseOnFailure' => false,
        ],
        'yiiQueue' => [
            'class' => \console\components\Queue::class,
            'db' => 'db',
            'tableName' => '{{%yii_queue}}',
            'attempts' => 2,
            'mutex' => \yii\mutex\MysqlMutex::class,
        ],
        'pusher' => [
            'class' => \common\components\pusher\Pusher::class,
            'appId' => env('PUSHER_APP_ID'),
            'appKey' => env('PUSHER_AUTH_KEY'),
            'appSecret' => env('PUSHER_SECRET'),
            'options' => [
                'encrypted' => true,
                'cluster' => env('PUSHER_CLUSTER'),
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'salesforce' => [
            'class' => SalesforceLeadService::class,
            'url' => env('SALESFORCE_URL'),
            'clientId' => env('SALESFORCE_CLIENT_ID'),
            'clientSecret' => env('SALESFORCE_CLIENT_SECRET'),
            'username' => env('SALESFORCE_CLIENT_USERNAME'),
            'password' => env('SALESFORCE_CLIENT_PASSWORD'),
            'securityToken' => env('SALESFORCE_CLIENT_SECURITY_TOKEN'),
            'webhookSecurityToken' => env('SALESFORCE_WEBHOOK_SECURITY_TOKEN'),
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '/',
        ],
        'fileSystem' => [
            'class' => 'creocoder\flysystem\LocalFilesystem',
            'path' => '@uploads/files',
        ],
        'awsS3FileSystem' => [
            'class' => 'creocoder\flysystem\AwsS3Filesystem',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET_KEY'),
            'bucket' => env('AWS_S3_BUCKET_NAME'),
            'region' => env('AWS_S3_REGION'),
            'prefix' => env('AWS_S3_BUCKET_DIRECTORY'),
            'options' => [
                'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                'ACL' => 'public-read',
            ],
        ],
        //initiated for each application separately
        'log' => [],
        'urlManagerBackend' => require(__DIR__ . '/../../backend/config/url-manager.php'),
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@themes/basic/mail',
            'messageConfig' => [
                'from' => [env('SMTP_FROM') => env('SMTP_TITLE')],
                'charset' => 'UTF-8',
            ],
            'useFileTransport' => env('MAIL_TO_FILE'),
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => env('SMTP_HOST'),
                'username' => env('SMTP_USERNAME'),
                'password' => env('SMTP_PASSWORD'),
                'port' => env('SMTP_PORT'),
                'encryption' => env('SMTP_ENCRYPTION'),
            ],
            'view' => [
                'theme' => [
                    'class' => 'common\components\Theme',
                    'basePath' => '@themes/basic',
                    'baseUrl' => '@frontendUrl/assets',
                ],
            ],
        ],
        'payment' => [
            'class' => 'modules\payment\components\Payment',
            'publicKey' => env('STRIPE_PUBLIC_KEY'),
            'privateKey' => env('STRIPE_PRIVATE_KEY'),
            'apiVersion' => env('STRIPE_API_VERSION'),
            'webhookSecret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'twilio' => [
            'class' => 'common\components\Twilio',
            'sid' => env('TWILLIO_SID'),
            'token' => env('TWILLIO_TOKEN'),
        ],
        'sms' => [
            'class' => 'modules\sms\components\Sms',
            'twilioNumber' => env('TWILLIO_NUMBER'),
        ],
        'formatter' => [
            'class' => \common\components\Formatter::class,
            'dateFormat' => 'MM-dd-yyyy',
            'decimalSeparator' => '.',
            'thousandSeparator' => ' ',
            'currencyCode' => 'USD',
        ],
        'geocoding' => [
            'class' => 'common\components\Geocoding',
        ],
        'shareasale' => [
            'class' => 'common\components\Shareasale',
            'merchantId' => env('SHAREASALE_MERCHANT_ID'),
            'token' => env('SHAREASALE_API_TOKEN'),
            'secret' => env('SHAREASALE_API_SECRET'),
        ],
        'roleMethod' => [
            'class' => \common\components\role\RoleMethod::class,
            'roles' => [
                Role::getRoleNameById(Role::ROLE_SPECIALIST) => [
                    'class' => \common\components\role\TutorMethod::class,
                ],
                Role::getRoleNameById(Role::ROLE_PATIENT) => [
                    'class' => \common\components\role\StudentMethod::class,
                ],
            ],
        ],
        'geoIp' => [
            'class' => 'common\components\GeoIp',
            'ip2locationDownloadToken' => env('IP2LOCATION_DOWNLOAD_TOKEN'),
            'maxmindDownloadToken' => env('MAXMIND_DOWNLOAD_TOKEN'),
        ],
        'fcm' => [
            'class' => Fcm::class,
            'apiVersion' => \aksafan\fcm\source\requests\StaticRequestFactory::API_V1,
            'apiParams' => [
                'privateKey' => Yii::getAlias('@common/config/push/firebase.json'),
            ],
        ],
        'pushMessage' => [
            'class' => \common\components\PushMessage::class,
        ],
        'googleTimeZone' => [
            'class' => \common\components\GoogleTimeZoneComponent::class,
            'key' => env('GOOGLE_TIME_ZONE_API_KEY'),
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => env('AUTH_GOOGLE_CLIENT_ID'),
                    'clientSecret' => env('AUTH_GOOGLE_CLIENT_SECRET'),
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => env('AUTH_FACEBOOK_CLIENT_ID'),
                    'clientSecret' => env('AUTH_FACEBOOK_CLIENT_SECRET'),
                    'attributeNames' => ['id', 'name', 'email'],
                    'scope' => 'public_profile,email',
                ],
            ],
        ],
        'filedb' => [
            'class' => 'yii2tech\filedb\Connection',
            'path' => '@root/',
        ],
    ],
    'controllerMap' => [
        'queue' => [
            'class' => 'UrbanIndo\Yii2\Queue\Console\Controller',
            //'sleepTimeout' => 1
        ],
    ],
    'modules' => [
        'core' => [
            'class' => 'modules\core\Module',
        ],
        'account' => [
            'class' => 'modules\account\Module',
            'onlineTutoringApiUrl' => env('ONLINE_TUTORING_REQUEST_URL'),
            'onlineTutoringApiKey' => env('ONLINE_TUTORING_API_KEY'),
        ],
        'payment' => [
            'class' => 'modules\payment\Module',
        ],
        'chat' => [
            'class' => 'modules\chat\Module',
            'application_id' => env('CHAT_APP_ID'),
            'auth_key' => env('CHAT_AUTH_KEY'),
            'secret_key' => env('CHAT_SECRET_KEY'),
            'endpoint_api' => env('CHAT_ENDPOINT_API'),
            'endpoint_chat' => env('CHAT_ENDPOINT_CHAT'),
            'suspiciousTutorsCount' => env('SUSPICIOUS_TUTORS_COUNT'),
            'tutorsCountCheckPeriod' => env('TUTORS_COUNT_CHECK_PERIOD'),
            'tutorsDoubleMessagesCountCheckPeriod' => env('TUTORS_DOUBLE_MESSAGES_COUNT_CHECK_PERIOD'),
            'doubleMessagesSuspiciousTutorsCount' => env('DOUBLE_MESSAGES_SUSPICIOUS_TUTORS_COUNT'),
        ],
        'notification' => [
            'class' => 'modules\notification\Module',
        ],
        'task' => [
            'class' => 'modules\task\Module',
        ],
        'analytics' => [
            'class' => 'modules\analytics\Module',
        ],
        'sms' => [
            'class' => 'modules\sms\Module',
        ],
        'yiiQueueHistory' => [
            'class' => \modules\yiiQueueHistory\Module::class,
        ],
    ],
];
