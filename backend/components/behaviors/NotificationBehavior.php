<?php

namespace backend\components\behaviors;

use yii\base\Behavior;
use yii\db\Expression;
use yii\base\Controller;
use modules\payment\models\Transaction;
use Yii;

/**
 * Class NotificationBehavior
 * @package common\components\behaviors
 * @deprecated
 */
class NotificationBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'notify',
        ];
    }

    /**
     * Notification for identity admin
     */
    public function notify()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $this->transactionNeedApprove();
    }

    /**
     * Notify, if not approved transactions exist
     */
    public function transactionNeedApprove()
    {
        $transactionExist = Transaction::find()
            ->byStatus(Transaction::STATUS_WAITING_FOR_APPROVE)
            ->exists();

        if ($transactionExist) {
                Yii::$app->session->setFlash('warning', "There are new payment transactions equal or more than 100$. Please moderate them");
        } else {
            Yii::$app->session->removeFlash('warning');
        }
    }
}
