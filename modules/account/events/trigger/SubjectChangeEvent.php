<?php

namespace modules\account\events\trigger;

use yii\base\Event;
use yii\base\Module;

class SubjectChangeEvent
{
    /**
     * @var $module Module
     */
    protected static $module;

    /**
     * SubjectChangeEvent constructor.
     */
    public static function init()
    {
        $module = self::$module = \Yii::$app->getModule('account');
        Event::on(
            Module::className(),
            $module::EVENT_SUBJECT_CHANGE,
            function ($event) use ($module) {
                $module->updateTutorSearchIndex(
                    $event->accountId
                );
            }
        );
    }
}
