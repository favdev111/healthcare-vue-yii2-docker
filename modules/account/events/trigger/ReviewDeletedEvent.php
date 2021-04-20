<?php

namespace modules\account\events\trigger;

use common\helpers\QueueHelper;
use common\models\Review;
use Yii;
use yii\base\Event;
use yii\base\Module;

class ReviewDeletedEvent
{
    /**
     * @var $module \modules\account\Module
     */
    protected static $module;

    /**
     * ReviewChangeEvent constructor.
     */
    public static function init()
    {
        self::$module = \Yii::$app->getModuleAccount();
        $module = self::$module;
        Event::on(Module::class, $module::EVENT_REVIEW_DELETED, function ($event) {
            /**
             * @var $model Review
             */
            $model = $event->model;

            if (
                $model->status === Review::ACTIVE
                && !$model->isAdmin
            ) {
                QueueHelper::setTutorRating($model->accountId);
            }

            return true;
        });
    }
}
