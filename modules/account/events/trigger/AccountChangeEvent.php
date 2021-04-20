<?php

namespace modules\account\events\trigger;

use common\helpers\QueueHelper;
use modules\account\models\Account;
use Yii;
use yii\base\Event;
use yii\base\Module;

class AccountChangeEvent
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
        Event::on(Module::class, $module::EVENT_ACCOUNT_CHANGE, function ($event) {
            /**
             * @var $account Account
             */
            $account = $event->model;

            if ($account->isTutor() && (isset($event->changedAttributes['email']))) {
                Yii::$app->payment->updateEmail($account);
            }

            if ($account->isTutor()) {
                QueueHelper::calculateTutorProfileUniqueWordsCount($account->id);
            }

            $updateTutorSearchIndex = false;
            if (isset($event->changedAttributes['hideProfile'])) {
                $updateTutorSearchIndex = true;
            }

            if (isset($event->changedAttributes['searchHide'])) {
                $updateTutorSearchIndex = true;
            }

            if (isset($event->changedAttributes['status'])) {
                $updateTutorSearchIndex = true;
            }

            if (isset($event->changedAttributes['blockReason'])) {
                $updateTutorSearchIndex = true;
            }

            if ($updateTutorSearchIndex) {
                self::$module->updateTutorSearchIndex($account->id);
            }

            return true;
        });
    }
}
