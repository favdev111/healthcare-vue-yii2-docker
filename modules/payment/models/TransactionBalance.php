<?php

namespace modules\payment\models;

use Yii;

/**
 * This is the model class for table "{{%transaction_balance}}".
 *
 * @property integer $id
 * @property integer $transactionId
 * @property string $balance
 * @deprecated usage not found, only post data to database
 */
class TransactionBalance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction_balance}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transactionId', 'balance'], 'required'],
            [['transactionId'], 'integer'],
            [['balance'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transactionId' => 'Transaction ID',
            'balance' => 'Balance',
        ];
    }
}
