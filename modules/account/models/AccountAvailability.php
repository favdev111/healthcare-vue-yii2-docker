<?php

namespace modules\account\models;

use common\helpers\AvailabilityHelper;
use Yii;

/**
 * This is the model class for table "account_availability".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $value
 * @property integer $popUpShown
 * @property array displayedValue - using for displaying data
 *
 * @property Account $account
 * @property array $mobileData
 */
class AccountAvailability extends \yii\db\ActiveRecord
{
    public $displayedValue = [];

    public function behaviors()
    {
        return [
            'biteString' => [
                'class' => 'common\components\behaviors\BiteStringBehavior',
                'attributeToSave' => 'value',
                'attributeToDisplay' => 'displayedValue',
                'biteStringLength' => AvailabilityHelper::BITE_STRING_LENGTH
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_availability}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId', 'value'], 'integer'],
            [['accountId'], 'unique'],
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['accountId' => 'id']],
            [['displayedValue'], 'safe'],
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
            'value' => 'Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    public function getMobileData()
    {
        return AvailabilityHelper::mobileData($this->displayedValue);
    }
}
