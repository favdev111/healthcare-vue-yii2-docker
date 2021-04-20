<?php

namespace modules\payment\models;

use Yii;

/**
 * This is the model class for table "{{%transaction_transfer}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $dateFrom
 * @property string $dateTo
 * @property integer $transactionId
 * @property integer $success
 * @property string $createdAt
 */
class TransactionTransfer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction_transfer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'dateFrom' => 'Date From',
            'dateTo' => 'Date To',
            'transactionId' => 'Transaction ID',
            'success' => 'Success',
            'createdAt' => 'Created At',
        ];
    }
}
