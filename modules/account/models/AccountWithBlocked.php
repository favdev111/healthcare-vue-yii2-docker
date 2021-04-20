<?php

namespace modules\account\models;

use modules\account\models\query\AccountQuery;

class AccountWithBlocked extends Account
{
    /**
     * @param $query AccountQuery
     * @return AccountQuery
     */
    protected static function addNonDeletedCondition($query)
    {
        return $query->notDeleted();
    }
}
