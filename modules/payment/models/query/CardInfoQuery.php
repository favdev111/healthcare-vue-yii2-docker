<?php

namespace modules\payment\models\query;

use common\components\ActiveQuery;
use yii\db\Expression;
use modules\payment\models\CardInfo;
use Yii;

/**
 * Class CardInfoQuery
 * @package namespace modules\payment\models\query;
 */
class CardInfoQuery extends ActiveQuery
{
    /**
     * add by accountId condition
     * @param $status
     * @return $this
     */
    public function byActiveStatus()
    {
        return $this->andWhere([$this->tableName . '.active' => CardInfo::STATUS_ACTIVE]);
    }

    /**
     * add by accountId condition
     * @param $accountId
     * @return $this
     */
    public function joinWithPaymentCustomer($accountId)
    {
        return $this->joinWith(['paymentCustomer' => function ($query) use ($accountId) {
            return $query->byAccountId($accountId);
        }
        ]);
    }

    /**
     * by active customer condition
     * @param $accountId
     * @return $this
     */
    public function byCustomerActiveCard($accountId)
    {
        return $this->joinWithPaymentCustomer($accountId)->byActiveStatus();
    }
}
