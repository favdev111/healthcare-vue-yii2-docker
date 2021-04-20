<?php

namespace modules\account\models\api\tutor;

use modules\account\models\AccountClientStatistic;
use modules\payment\components\Payment;
use modules\payment\Module;
use Yii;
use yii\base\NotSupportedException;

/**
 * @inheritdoc
 *
 * @property Account $company
 */
class Reviewer extends \modules\account\models\Account
{
    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = [
            'profile',
        ];

        return $fields;
    }
}
