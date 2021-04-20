<?php

namespace modules\account\models\api;

class AccountWithDeleted extends Account
{
    public static function find()
    {
        return parent::findWithoutRestrictions();
    }

    public static function findByCondition($condition)
    {
        return parent::findByConditionWithoutRestrictions($condition);
    }

    public static function findOne($condition)
    {
        return parent::findOneWithoutRestrictions($condition);
    }
}
