<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "{{%phone_validation}}".
 *
 * @property integer $id
 * @property integer $phoneId
 * @property $response
 * @property integer $type
 * @property integer $status
 * @property string phoneNumber
 *
 * @property AccountPhone $relatedPhone
 */
class PhoneValidation extends \yii\db\ActiveRecord
{
    const TYPE_LANDLINE = 1;
    const TYPE_MOBILE = 2;
    const TYPE_VOIP = 3;

    const STATUS_INVALID = 0;
    const STATUS_VALID = 1;

    public static $typeLabels = [
        'landline' => self::TYPE_LANDLINE,
        'mobile' => self::TYPE_MOBILE,
        'voip' => self::TYPE_VOIP
    ];

    public static $validTypes = [self::TYPE_LANDLINE, self::TYPE_MOBILE, self::TYPE_VOIP];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%phone_validation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['response'], 'required'],
            [['response'], function () {
                if (is_object($this->response)) {
                    $this->response = serialize($this->response);
                }
            }
            ],
            [['phoneId', 'type', 'status'], 'integer'],
            [['phoneNumber'], 'string', 'max' => 12],
            [['phoneId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountPhone::className(), 'targetAttribute' => ['phoneId' => 'id']],
            ['type', 'default',  'value' => function () {
                if (!empty($this->response) && is_object($this->response)) {
                    $typeLabel =  $this->response->carrier['type'] ?? null;
                    if (!empty($typeLabel)) {
                        return static::getTypeByLabel($typeLabel);
                    }
                }
                return null;
            }
            ],
            ['status', 'default', 'value' => function () {
                return !empty($this->type) ? static::isTypeValid($this->type) : self::STATUS_INVALID;
            }
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
            'phoneId' => 'Phone ID',
            'response' => 'Response',
            'type' => 'Type',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedPhone()
    {
        return $this->hasOne(AccountPhone::className(), ['id' => 'phoneId']);
    }

    /**
     * @inheritdoc
     * @return \modules\account\models\query\PhoneValidationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\PhoneValidationQuery(get_called_class());
    }

    public static function getTypeByLabel(string $label): int
    {
        return self::$typeLabels[$label];
    }

    public static function isTypeValid(int $type): bool
    {
        return in_array($type, self::$validTypes);
    }
}
