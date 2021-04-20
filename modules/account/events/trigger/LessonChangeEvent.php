<?php

namespace modules\account\events\trigger;

use common\helpers\QueueHelper;
use modules\account\events\ChangeEvent;
use Yii;
use yii\base\Event;
use yii\base\Module;

class LessonChangeEvent
{
    /**
     * @var $module \modules\account\Module
     */
    protected static $module;

    /**
     * AccountChangeEvent constructor.
     */
    public static function init()
    {
        self::$module = Yii::$app->getModuleAccount();
        $module = self::$module;
        Event::on(Module::class, $module::EVENT_LESSON_CHANGE, function (ChangeEvent $event) {
            QueueHelper::setTutorRating($event->model->tutorId);
        });
    }
}
