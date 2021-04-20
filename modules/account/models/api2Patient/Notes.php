<?php

namespace modules\account\models\api2Patient;

use Yii;

class Notes extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%account_note}}';
    }
  
    public function rules()
    {
        return [
            'required' => [['accountId', 'content','createdAt','createdBy'], 'required'],
            [['content','createdAt'], 'string'],
            [['createdAt'], 'date', 'format' => 'php: Y-m-d H:i:s', 'min' => '1900-01-01 00:00:00'],
            [
                'createdAt',
                'date',
                'format' => 'php:Y-m-d H:i:s',
                'timestampAttribute' => 'createdAt',
                'timestampAttributeFormat' => 'php:Y-m-d H:i:s'
            ],
            [['accountId'], 'integer', 'min' => '1'],
            [
                'accountId',
                'integer',
                'min' => '1'
            ],
            [['createdBy'], 'integer', 'min' => '1'],
            [
                'createdBy',
                'integer',
                'min' => '1'
            ],
        ];
    }
}
