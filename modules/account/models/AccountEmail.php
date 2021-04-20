<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use common\components\validators\MailRuValidator;
use Yii;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%account_email}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $email
 * @property integer $isPrimary
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $account
 */
class AccountEmail extends \yii\db\ActiveRecord
{
    protected static $accountClass = Account::class;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_email}}';
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
            [['accountId', 'email', '!createdAt'], 'required'],
            [['accountId'], 'integer'],
            [['email'], 'string', 'max' => 255],
            [['email'], MailRuValidator::class, 'on' => 'create'],
            ['email', 'email', 'checkDNS' => true, 'when' => function ($model) {
                if ($model instanceof BaseActiveRecord) {
                    return $model->isAttributeChanged('email');
                }
                return true;
            }
            ],
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
            'email' => 'Email',
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
     * @inheritdoc
     * @return \modules\account\models\query\AccountEmailQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\AccountEmailQuery(get_called_class());
    }
}
