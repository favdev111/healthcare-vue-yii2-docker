<?php

namespace modules\payment\models\query;

use modules\payment\models\PaymentProcess;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\modules\payment\models\PaymentProcess]].
 *
 * @see \modules\payment\models\PaymentProcess
 */
class PaymentProcessQuery extends \yii\db\ActiveQuery
{
    public function byStatus(int $status): ActiveQuery
    {
        return $this->andWhere([PaymentProcess::tableName() . '.status' => $status]);
    }
}
