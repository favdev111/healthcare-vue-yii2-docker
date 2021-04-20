<?php

namespace modules\account\models\api;

use modules\account\Module;
use modules\payment\models\Transaction;
use Yii;
use yii\db\ActiveQueryInterface;

/**
 * This is the model class for table "client_balance_transaction".
 *
 * @property integer $id
 * @property integer $clientId
 * @property string $amount
 * @property integer $type
 *
 * @property Account $client
 * @property Transaction $transaction
 */
class ClientBalanceTransaction extends \modules\account\models\ClientBalanceTransaction
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules['clientIdExist'] = [['clientId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountClient::className(), 'targetAttribute' => ['clientId' => 'id']];
        return $rules;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(AccountClient::className(), ['id' => 'clientId']);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByCondition(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::findByCondition($condition);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        return $query->all();
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['client'] = 'client';
        $extraFields[] = 'transaction';
        $extraFields['refundButton'] = function () {
            $isClientBalance =  (!empty($this->transaction)) ? $this->transaction->isClientBalance() : false;
            if (!empty($this->transaction)) {
                 $notRefundedSum = $this->transaction->calculateNotRefundedSum();
            } else {
                $notRefundedSum = false;
            }
            return $notRefundedSum && $isClientBalance;
        };
        $extraFields['sumRefund'] = function () {
            if (empty($this->transaction)) {
                return null;
            }
            //for refunded lessons show refunded sum equal to client balance amount (because lesson can be refunded only fully)
            if (
                ($this->transaction->isLesson() || $this->transaction->isLessonBatchPayment())
                && $this->transaction->isTypeRefund()
            ) {
                return $this->amount ? abs((double)$this->amount) :  null;
            }
            if ($this->transaction->isHasPartialRefunds()) {
                return $this->transaction->getTotalPartialRefundSum();
            } elseif ($this->transaction->type === Transaction::STRIPE_REFUND) {
                return (double)$this->transaction->amount;
            }
            return null;
        };


        return $extraFields;
    }
}
