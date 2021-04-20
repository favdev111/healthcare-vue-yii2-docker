<?php

namespace common\models;

use modules\account\models\Account;
use Yii;

/**
 * This is the model class for table "{{%account_terms}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $termsSigned
 * @property integer $isTermDocCreated
 *
 * @property Account $account
 */
class AccountTerms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_terms}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId'], 'required'],
            [['accountId'], 'integer'],
            [['termsSigned', 'isTermDocCreated'], 'boolean'],
            [['accountId'], 'unique'],
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['accountId' => 'id']],
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
            'termsSigned' => 'Terms Signed',
            'isTermDocCreated' => 'Is Term Doc Created',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }
}
