<?php

namespace modules\payment\models\api;

use modules\account\models\api\AccountEmployee;
use yii\db\ActiveQueryInterface;

/**
 * Trait OwnEmployeeTrait
 * This trait overrides find(), findOne(), findByCondition(), findBySql(), findAll() methods to select data that belongs to company.
 * To change this methods - define them in class.
 * @package modules\payment\models\api
 */
trait OwnEmployeeTrait
{
    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        static::addOwnEmployeeCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByCondition(['id' => $id]);
        static::addOwnEmployeeCondition($query);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::find($condition);
        static::addOwnEmployeeCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        static::addOwnEmployeeCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        static::addOwnEmployeeCondition($query);
        return $query->all();
    }


    /**
     * @param $query ActiveQueryInterface
     * @return mixed
     * @throws \yii\base\NotSupportedException
     */
    protected static function addOwnEmployeeCondition($query)
    {
        $ownClientsQuery = AccountEmployee::find()->select('id');
        return $query->andWhere([static::tableName() . '.accountId' => $ownClientsQuery]);
    }
}
