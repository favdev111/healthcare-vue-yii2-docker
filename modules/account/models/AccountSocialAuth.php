<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "account_social_auth".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $source
 * @property string $sourceId
 *
 * @property Account $account
 */
class AccountSocialAuth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_social_auth}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }
}
