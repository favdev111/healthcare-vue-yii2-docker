<?php

namespace modules\account\events\trigger;

use common\helpers\QueueHelper;
use modules\account\models\Profile;
use Yii;
use yii\base\Event;
use yii\base\Module;

class ProfileChangeEvent
{
    /**
     * @var $module Module
     */
    protected static $module;

    /**
     * ProfileChangeEvent constructor.
     */
    public static function init()
    {
        /**
         * @var $module \modules\account\Module
         */
        $module = self::$module = \Yii::$app->getModule('account');
        Event::on(
            Module::className(),
            $module::EVENT_PROFILE_CHANGE,
            function ($event) use ($module) {
                /**
                 * @var $profile Profile
                 */
                $profile = $event->model;

                if ($profile->account->isTutor()) {
                    QueueHelper::calculateTutorProfileUniqueWordsCount($event->model->accountId);
                }

                if ($event->insert) {
                    return;
                }

                $changedAttributes = $profile->getChangedAttributes();
                if (
                    $profile->account->isCrmAdmin()
                    && (
                        in_array('firstName', $changedAttributes)
                        || in_array('lastName', $changedAttributes)
                    )
                ) {
                    Yii::$app->payment->updateCompanyContactName(
                        $profile->account,
                        $profile->firstName,
                        $profile->lastName
                    );
                }

                $module->updateTutorSearchIndex(
                    $event->model->accountId
                );
            }
        );
    }
}
