<?php

namespace modules\account\models\backend;

use modules\account\models\Lesson;
use Yii;

/**
 * @inheritdoc
 */
class Review extends \common\models\Review
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                ['name', 'required',
                    'on' => self::SCENARIO_BACKEND_ADD,
                    'when' => function ($model) {
                        return $this->message != '';
                    },
                    'whenClient' => 'function() {
                        return false;
                    }'
                ],
                ['message', 'required',
                    'on' => self::SCENARIO_BACKEND_ADD,
                    'when' => function ($model) {
                        return $this->name != '';
                    },
                    'whenClient' => 'function() {
                        return false;
                    }'
                ],
                [
                    ['hours', 'accounts'],
                    'required',
                    'on' => self::SCENARIO_BACKEND_ADD,
                    'when' => function ($model) {
                        return $this->message == '' && $this->name == '';
                    },
                    'whenClient' => 'function() {
                        return false;
                    }',
                ],
                [['hours', 'accounts'], 'default', 'value' => 0, 'when' => function ($model) {
                    return $this->message != '' && $this->name != '';
                },
                ],
                [['createdAt'], 'required']
            ]
        );
    }
}
