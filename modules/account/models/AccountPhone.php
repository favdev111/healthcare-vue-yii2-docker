<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use common\components\HtmlPurifier;
use Yii;

/**
 * This is the model class for table "{{%account_phone}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $phoneNumber
 * @property integer $isPrimary
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $account
 * @property PhoneValidation[] $phoneValidation
 */
class AccountPhone extends \yii\db\ActiveRecord
{
    protected static $accountClass = Account::class;

    //using for creating PhoneValidation Model
    public $validationResponse;
    public $validationPhoneType;
    public $validationPhoneStatus;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_phone}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => TimestampBehavior::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId', 'phoneNumber', '!createdAt'], 'required'],
            [['accountId'], 'integer'],
            [['phoneNumber'], 'string', 'max' => 12],
            [['phoneNumber'], 'match', 'pattern' => '/^\d+$/'],
            ['phoneNumber', 'udokmeci\yii2PhoneValidator\PhoneValidator', 'country' => 'US', 'format' => false],
            [['isPrimary'], 'string', 'max' => 1],
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => static::$accountClass, 'targetAttribute' => ['accountId' => 'id']],
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
            'phoneNumber' => 'Phone Number',
            'isPrimary' => 'Is Primary',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(static::$accountClass, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoneValidation()
    {
        return $this->hasOne(PhoneValidation::class, ['phoneId' => 'id']);
    }


    /**
     * @inheritdoc
     * @return \modules\account\models\query\AccountPhoneQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\AccountPhoneQuery(get_called_class());
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (!empty($this->validationResponse)) {
            $validationModel = $this->phoneValidation;
            if (empty($validationModel)) {
                $validationModel = new PhoneValidation();
                $validationModel->phoneId = $this->id;
            }
            $validationModel->response = $this->validationResponse;
            $validationModel->phoneId = $this->id;
            $validationModel->type = $this->validationPhoneType;
            $validationModel->status = $this->validationPhoneStatus;
            $validationModel->save();
        }
    }
}
