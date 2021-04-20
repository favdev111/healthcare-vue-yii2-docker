<?php

namespace modules\account\models\api;

use common\components\validators\TwilioPhoneValidator;

class AccountPhone extends \modules\account\models\AccountPhone
{
    public static $accountClass = Account::class;

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['phoneNumber', TwilioPhoneValidator::class]
        ]);
    }

    public function fields()
    {
        return [
            'phoneNumber',
            'isPrimary',
            'createdAt',
            'updatedAt',
        ];
    }

    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
            'phoneValidation'
        ]);
    }
}
