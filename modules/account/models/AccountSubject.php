<?php

namespace modules\account\models;

use Yii;
use common\components\HtmlPurifier;

/**
 * This is the model class for table "{{%account_subject}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $subjectId
 * @property string $description
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $account
 * @property Subject $subject
 */
class AccountSubject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_subject}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['subjectId', 'description', 'createdAt'], 'required'],
            [['subjectId'], 'integer'],
            [['description'], 'string'],
            [['subjectId'], 'unique', 'targetAttribute' => ['accountId', 'subjectId', 'isCategory'], 'message' => 'The combination of Account ID and Subject ID has already been taken.'],
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
            'subjectId' => 'Subject ID',
            'description' => 'Description',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::className(), ['id' => 'subjectId']);
    }
}
