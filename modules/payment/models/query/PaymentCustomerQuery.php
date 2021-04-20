<?php

namespace modules\payment\models\query;

use common\components\ActiveQuery;
use yii\db\Expression;
use modules\payment\models\CardInfo;
use Yii;

/**
 * Class PaymentCustomerQuery
 * @package namespace modules\payment\models\query;
 */
class PaymentCustomerQuery extends ActiveQuery
{
    /**
     * add by accountId condition
     * @param $accountId
     * @return $this
     */
    public function byAccountId($accountId)
    {
        return $this->andWhere([$this->tableName . '.accountId' => $accountId]);
    }
}
