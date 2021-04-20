<?php

namespace modules\account\models\api;

class AccountEmail extends \modules\account\models\AccountEmail
{
    public static $accountClass = AccountClient::class;

    public function fields()
    {
        return [
            'email',
            'isPrimary',
            'createdAt',
            'updatedAt',
        ];
    }
}
