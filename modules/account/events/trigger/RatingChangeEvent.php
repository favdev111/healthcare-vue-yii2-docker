<?php

namespace modules\account\events\trigger;

use common\helpers\QueueHelper;
use yii\base\Event;
use yii\base\Module;

class RatingChangeEvent
{
    /**
     * @var $module Module
     */
    protected static $module;

    /**
     * RatingChangeEvent constructor.
     */
    public static function init()
    {
        $module = self::$module = \Yii::$app->getModuleAccount();
        Event::on(
            Module::class,
            $module::EVENT_RATING_CHANGE,
            function ($event) use ($module) {
                QueueHelper::setTutorScore($event->accountId);
            }
        );
    }
}
