<?php

namespace modules\account\models\api;

use modules\payment\models\api\OwnEmployeeTrait;

class AccountTeam extends \modules\account\models\AccountTeam
{
    use OwnEmployeeTrait;

    //use in rules and relations
    public static $accountClass = Account::class;
}
