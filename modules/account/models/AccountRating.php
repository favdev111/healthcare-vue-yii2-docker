<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use modules\account\helpers\EventHelper;
use Yii;

/**
 * This is the model class for table "{{%account_rating}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $totalArticulation
 * @property integer $totalProficiency
 * @property integer $totalPunctual
 * @property integer $totalHours
 * @property integer $totalAccounts
 * @property float $avgResponseTime
 * @property string $createdAt
 * @property string $updatedAt
 */
class AccountRating extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_rating}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['totalAccounts'], 'integer'],
            [['avgResponseTime', 'totalArticulation', 'totalProficiency', 'totalRating', 'totalPunctual', 'totalHours'], 'double'],
            [['totalArticulation', 'totalProficiency', 'totalRating', 'totalPunctual', 'totalHours'], 'default', 'value' => 0]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
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
            'totalArticulation' => 'Total Articulation',
            'totalProficiency' => 'Total Proficiency',
            'totalPunctual' => 'Total Punctual',
            'totalHours' => 'Total Hours',
            'totalAccounts' => 'Total Accounts',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (
            !$insert
            && isset($changedAttributes['totalRating'])
        ) {
            EventHelper::changeRatingEvent(
                $this->accountId,
                $this->totalRating,
                $changedAttributes['totalRating']
            );
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
