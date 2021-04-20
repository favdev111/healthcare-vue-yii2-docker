<?php

namespace modules\payment\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[PlatformPayouts]].
 *
 * @see PlatformPayout
 */
class PlatformPayoutQuery extends \yii\db\ActiveQuery
{
    public function byStripeId(string $stripeId): ActiveQuery
    {
        return $this->andWhere(['stripeId' => $stripeId]);
    }
}
