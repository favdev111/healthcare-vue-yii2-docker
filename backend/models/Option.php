<?php

namespace backend\models;

use yii\base\BaseObject;
use yii\db\Query;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii2tech\filedb\Query as QueryFile;

/**
 * Class Option
 * @package backend\models
 */
abstract class Option extends BaseObject
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @param $name
     * @param QueryInterface $query
     * @param string $columnValue
     * @param string $columnIndex
     * @return mixed
     * @throws \Exception
     */
    protected function getOptions($name, QueryInterface $query, string $columnValue, string $columnIndex = 'id')
    {
        $valueExist = ArrayHelper::keyExists($name, $this->values);
        if (!$valueExist) {
            $this->values[$name] = self::getValuesByQuery($query, $columnValue, $columnIndex);
        }

        return $this->values[$name];
    }

    /**
     * @param QueryInterface $query
     * @param string $columnValue
     * @param string $columnIndex
     * @return array
     */
    private static function getValuesByQuery(QueryInterface $query, string $columnValue, string $columnIndex)
    {
        return match (true) {
            $query instanceof QueryFile => self::getOptionByQueryFile($query, $columnValue, $columnIndex),
            $query instanceof Query => self::getOptionByQueryDB($query, $columnValue, $columnIndex),
        };
    }

    /**
     * @param Query $query
     * @param string $columnValue
     * @param string $columnIndex
     * @return array
     */
    private static function getOptionByQueryDB(Query $query, string $columnValue, string $columnIndex = 'id'): array
    {
        $items = $query->indexBy($columnIndex)
            ->select([$columnValue])
            ->column();
        foreach ($items as &$item) {
            $item = StringHelper::mb_ucfirst($item);
        }
        return $items;
    }

    /**
     * @param QueryFile $query
     * @param string $columnValue
     * @param string $columnIndex
     * @return array
     */
    private static function getOptionByQueryFile(QueryFile $query, string $columnValue, string $columnIndex = 'id'): array
    {
        return $query->indexBy($columnIndex)
            ->column(static function ($model) use ($columnValue) {
                return StringHelper::mb_ucfirst($model[$columnValue]);
            });
    }
}
