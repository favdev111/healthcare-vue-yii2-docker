<?php

namespace modules\payment\models\api;

use modules\account\models\api\AccountClient;
use modules\account\models\api\Lesson;
use modules\account\models\Token;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class Transaction extends \modules\payment\models\Transaction
{
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

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['response']);
        unset($fields['transactionExternalId']);
        unset($fields['bankTransactionId']);
        $fields['amount'] = function () {
            return (float)$this->amount;
        };
        $fields['fee'] = function () {
            return (float)$this->fee;
        };
        $fields['lesson'] = function () {
            return $this->isLesson() || $this->isLessonBatchPayment() ? $this->lesson : null;
        };
        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['isAllowedReCharge'] = function () {
            return $this->isAllowedReCharge();
        };
        $extraFields['client'] = 'client';
        $extraFields['isPartialRefundOfGroupTransaction'] = function () {
            return $this->isPartialRefundOfGroupTransaction();
        };

        return $extraFields;
    }

    public static function findWithoutConditions()
    {
        return parent::find();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLesson()
    {
        return $this->hasOne(Lesson::class, ['id' => 'objectId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(AccountClient::class, ['id' => 'studentId']);
    }
}
