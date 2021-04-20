<?php

namespace common\models;

use common\helpers\EmailHelper;
use common\models\query\TutorProQuery;
use modules\account\Module;
use Yii;
use common\components\HtmlPurifier;

/**
 * This is the model class for table "{{%tutor_pro}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property string $message
 * @property string $createdAt
 * @property string $viewed
 */
class TutorPro extends \yii\db\ActiveRecord
{

    const STATUS_VIEWED_TRUE = 1;
    const STATUS_VIEWED_FALSE = 0;


    const VIEWED_STATUSES = [
        self::STATUS_VIEWED_TRUE => 'viewed',
        self::STATUS_VIEWED_FALSE => 'new',
    ];

    /**
     * @return TutorProQuery
     */
    public static function find()
    {
        return new TutorProQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tutor_pro}}';
    }

    /**
     * Update status viewed for pro service request
     * @return bool
     */
    public function updateStatusViewed()
    {
        $this->setAttribute('viewed', self::STATUS_VIEWED_TRUE);
        return $this->save(false);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            /**
             * @var Module $module
             */
            $module = Yii::$app->getModule('account');

            $module->eventTutorProRequest($this);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Is new request
     * @return bool
     */
    public function isNew()
    {
        return $this->viewed === self::STATUS_VIEWED_FALSE;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'message'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['name', 'phone', 'email', 'message'], 'required'],
            [['message'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['name', 'phone', 'email', 'message'], 'trim'],
            [['email'], 'email'],
            [['viewed'], 'in', 'range' => [self::STATUS_VIEWED_FALSE, self::STATUS_VIEWED_TRUE]],

            ['phone', 'string', 'max' => 10],
            ['phone', 'udokmeci\yii2PhoneValidator\PhoneValidator','country' => 'US', 'format' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'message' => 'Message',
            'createdAt' => 'Created At',
        ];
    }
}
