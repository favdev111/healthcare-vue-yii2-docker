<?php

namespace modules\account\models;

use common\components\HtmlPurifier;
use Yii;

/**
 * This is the model class for table "{{%account_reward}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $certificationOrg
 * @property string $certificateName
 * @property integer $yearReceived
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $account
 */
class Reward extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_reward}}';
    }

    public function formName()
    {
        return 'rewards';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['certificationOrg', 'certificateName'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['certificateName', 'yearReceived'], 'required'],
            [['certificationOrg'],'required', 'message' => 'Organization cannot be blank'],
            ['yearReceived', 'integer', 'min' => 1900],
            [['certificationOrg', 'certificateName'], 'string'],
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
            'certificationOrg' => 'Certification Org',
            'certificateName' => 'Certificate Name',
            'yearReceived' => 'Year Received',
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
}
