<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "{{%change_rate}}".
 *
 * @property integer $id
 * @property string $rate
 * @property string $accountId
 * @property string $createdAt
 */
class ChangeRate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%change_rate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rate', 'accountId'], 'required'],
            [['rate'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rate' => 'Rate',
            'accountId' => 'Account ID',
            'createdAt' => 'Created At',
        ];
    }
}
