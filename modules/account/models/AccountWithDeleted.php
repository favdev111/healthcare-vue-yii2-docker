<?php

namespace modules\account\models;

/**
 * @inheritdoc
 */
class AccountWithDeleted extends Account
{
    /**
     * @inheritdoc
     */
    public static function findOne($condition)
    {
        return parent::findOneWithoutRestrictions($condition);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::findWithoutRestrictions();
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        return parent::findByConditionWithoutRestrictions($condition);
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        return parent::findBySqlWithoutRestrictions($sql, $params);
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        return self::findAllWithoutRestrictions($condition);
    }
}
