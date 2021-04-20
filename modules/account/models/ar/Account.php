<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;
use modules\account\models\query\AccountQuery;

/**
 * Class Account
 * @property string $email
 * @property string $passwordHash
 * @property string $publicId
 * @property int $roleId
 * @property int $status
 * @package modules\account\models\ar
 */
class Account extends ActiveRecord
{
    public function rules()
    {
        return [
            [
                ['email', 'roleId', 'passwordHash', 'publicId', 'createdIp'],
                'safe'
            ]
        ];
    }

    public static function find()
    {
        return new AccountQuery(self::class);
    }
}
