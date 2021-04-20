<?php

namespace modules\account\models;

use modules\account\helpers\ConstantsHelper;
use modules\account\Module;
use Yii;

/**
 * This is the model class for table "{{%account_client_statistic}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $tutorId
 * @property double $hoursBilled
 *
 * @property Account $account
 */
class AccountCompanyStatistic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_company_statistic}}';
    }

    public function rules()
    {
        return [
            [['hoursBilled'], 'default', 'value' => 0],
            [['tutorId'], 'unique', 'targetAttribute' => ['accountId', 'tutorId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'createdAtAttribute' => null,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    public static function datesDiffToDecimalHours(string $toDate, string $fromDate): float
    {
        $secondsInHour = 3600;
        return (strtotime($toDate) - strtotime($fromDate)) / $secondsInHour;
    }
}
