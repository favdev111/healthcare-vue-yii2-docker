<?php

/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 * IMPORTANT: This file is not used in application execution process at all
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication|\common\components\app\CommonApplication
     * the application instance
     */
    public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 *
 * @property \modules\payment\components\Payment $payment The payment component.
 * This property is read-only. Extended component.
 * @property common\components\Geocoding $geocoding The geocoding component
 * @property \UrbanIndo\Yii2\Queue\Queue $queue The Queue component
 * @property \console\components\Queue $yiiQueue The Yii Queue component
 * @property \common\components\View $view The View component
 * @property \common\components\PushMessage $pushMessage The PushMessage component
 * @property \modules\payment\components\TransferHandlerService $transferHandlerService Transfer Handler Service
 * (TODO: Refactoring required)
 * @property \common\components\SalesforceLeadService $salesforce Service for work with Salesforce REST API
 * @property \common\components\PhoneNumberService $phoneNumber Phone Number service (phone, zipcode, etc)
 * @property \common\components\Formatter $formatter
 * @property \common\components\pusher\Pusher $pusher
 * @property \common\components\StripePlatformAccount $stripePlatformAccount
 * @property \creocoder\flysystem\Filesystem $fileSystem
 * @property \modules\core\components\Settings $settings
 * @property \common\components\GoogleTimeZoneComponent $googleTimeZone
 * @property \modules\account\components\RegistrationService $registration
 * @property \creocoder\flysystem\AwsS3Filesystem $awsS3FileSystem
 * @property \tuyakhov\notifications\Notifier $notifier
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 *
 * @property \modules\account\components\Account $user The user component.
 * This property is read-only. Extended component.
 * @property \common\components\UrlManager $urlManager The urlManager component.
 * This property is read-only. Extended component.
 */
class WebApplication extends yii\web\Application
{
}

/**
 * Class ConsoleApplication
 * Include only Console application related components here
 *
 */
class ConsoleApplication extends yii\console\Application
{
}
