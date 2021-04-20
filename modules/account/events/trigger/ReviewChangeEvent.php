<?php

namespace modules\account\events\trigger;

use common\helpers\QueueHelper;
use common\models\Review;
use Yii;
use yii\base\Event;
use yii\base\Module;

class ReviewChangeEvent
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
        Event::on(Module::class, $module::EVENT_REVIEW_CHANGE, function ($event) {
            /**
             * @var $model Review
             */
            $model = $event->model;
            $changedAttributes = $event->changedAttributes;

            if (
                !$model->isAdmin
                && (
                    (
                        isset($changedAttributes['status'])
                        && $changedAttributes['status'] != $model->status
                        && !(
                            $changedAttributes['status'] == Review::NEW
                            && $model->status == Review::BANNED
                        )
                    )
                    ||
                    (
                        $model->status == Review::ACTIVE
                        && (
                            (
                                isset($changedAttributes['articulation'])
                                && $changedAttributes['articulation'] != $model->articulation
                            )
                            || (
                                isset($changedAttributes['proficiency'])
                                && $changedAttributes['proficiency'] != $model->proficiency
                            )
                            || (
                                isset($changedAttributes['punctual'])
                                && $changedAttributes['punctual'] != $model->punctual
                            )
                        )
                    )
                )
            ) {
                QueueHelper::setTutorRating($model->accountId);
            }

            QueueHelper::calculateTutorProfileUniqueWordsCount($model->accountId);

            return true;
        });
    }
}
