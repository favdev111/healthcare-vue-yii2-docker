<?php

namespace modules\account\models\api2;

use Yii;

trait AccountTrait
{
    public $accessToken;

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = [
            'id',
            'email',
            'avatarUrl',
            'profile',
        ];

        if ($this->accessToken) {
            array_unshift($fields, 'accessToken');
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extraFields = [];
        $extraFields['chat'] = 'chat';
        $extraFields['subjects'] = 'accountSubjects';

        return $extraFields;
    }
}
