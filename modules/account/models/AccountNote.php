<?php

namespace modules\account\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\helpers\Console;

/**
 * This is the model class for table "account_note".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $content
 * @property string $createdAt
 * @property string $createdBy
 * @property string $updatedAt
 * @property string $updatedBy
 * @property boolean $isPinned
 *
 * @property Account $account
 */
class AccountNote extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_note}}';
    }

    public function rules()
    {
        return [
            [['content'], 'required'],
            [['isPinned'], 'boolean'],
            [['isPinned'], 'default', 'value' => false],
            [['content'], 'string', 'max' => 65535],
        ];
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => 'updatedBy',
                'value' => Yii::$app->user->id
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
            'content' => 'Note',
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

    public function beforeSave($insert)
    {
        if ($this->isAttributeChanged('isPinned') && !$insert) {
            $this->detachBehavior('timestamp');
        }
        return parent::beforeSave($insert);
    }
}
