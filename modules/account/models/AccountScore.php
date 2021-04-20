<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "{{%account_score}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $hoursScore
 * @property string $ratingScore
 * @property string $responseTimeScore
 * @property string $contentScore
 * @property int $totalScore
 *
 * @property Account $account
 */
class AccountScore extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_score}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'hoursScore' => 'Hours Score',
            'ratingScore' => 'Rating Score',
            'responseTimeScore' => 'Response Time Score',
            'contentScore' => 'Content Score',
            'totalScore' => 'Total Score',
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
