<?php

namespace modules\account\models\backend;

/**
 * @inheritdoc
 */
class AccountClient extends \modules\account\models\api\AccountClient
{
    /**
     * @inheritdoc
     */
    public static function find()
    {
        return Account::find()
            ->notEmployee();
    }
}
