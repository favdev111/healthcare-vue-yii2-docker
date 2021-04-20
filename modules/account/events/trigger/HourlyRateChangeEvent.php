<?php

namespace modules\account\events\trigger;

use modules\account\models\Account;
use yii\base\Event;
use yii\base\Module;

class HourlyRateChangeEvent
{
    /**
     * @var $module Module
     */
    protected static $module;

    /**
     * HourlyRateChangeEvent constructor.
     */
    public static function init()
    {
        $module = self::$module = \Yii::$app->getModule('account');
        Event::on(
            Module::class,
            $module::EVENT_HOURLY_RATE_CHANGE,
            function ($event) use ($module) {
                $account = Account::findWithAllStatus($event->accountId);
                $module->updateTutorSearchIndex(
                    $event->accountId,
                    [
                        'hourlyRate' => (float)$event->rateModel->getFullRate($account),
                        'clearHourlyRate' => (float)$event->rateModel->hourlyRate,
                    ]
                );
            }
        );
    }
}
