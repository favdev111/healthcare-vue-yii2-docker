<?php

namespace modules\payment\models\backend;

use yii\bootstrap\Html;

class Transaction extends \modules\payment\models\Transaction
{
    public function getPaidForString()
    {
        switch ($this->objectType) {
            case Transaction::TYPE_LESSON:
                return Html::a(
                    $this->lesson->subject->name,
                    ['/account/lesson/view', 'id' => $this->objectId],
                    ['data-pjax' => 0]
                );
                break;
            case Transaction::TYPE_ACCOUNT:
                return Html::a(
                    $this->account->email,
                    ['/account/tutor/view', 'id' => $this->objectId],
                    ['data-pjax' => 0]
                );
                break;
            default:
                return Transaction::$typesObject[$this->objectType] ?? 'Unknown';
                break;
        }
    }
}
