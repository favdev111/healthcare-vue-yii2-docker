<?php

namespace modules\account\models;

/**
 * @inheritdoc
 * Overriding find methods to execute base ones instead
 * Required to use this model in hasOne and hasMany, in backend etc.
 */
class JobWithSuspended extends Job
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
