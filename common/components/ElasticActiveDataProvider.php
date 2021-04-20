<?php

namespace common\components;

use yii\base\InvalidConfigException;
use yii\elasticsearch\ActiveDataProvider;
use yii\elasticsearch\Query;

class ElasticActiveDataProvider extends ActiveDataProvider
{
    public static $returnedClass = null;
    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof Query) {
            throw new InvalidConfigException('The "query" property must be an instance "' . Query::className() . '" or its subclasses.');
        }

        $query = clone $this->query;
        //select only ids - use it for getting objects of $returnedClass
        if (!empty(static::$returnedClass)) {
            $query->source = ['accountId'];
        }

        if (($pagination = $this->getPagination()) !== false) {
            // pagination fails to validate page number, because total count is unknown at this stage
            $pagination->validatePage = false;
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        $results = $query->search($this->db);
        $this->setQueryResults($results);

        if ($pagination !== false) {
            $pagination->totalCount = $this->getTotalCount();
        }

        if (empty(static::$returnedClass)) {
            $result = $results['hits']['hits'];
        } else {
            $listIds = [];
            foreach ($results['hits']['hits'] as $item) {
                $listIds[$item->getPrimaryKey()] = $item->getScore();
            }
            $class = static::$returnedClass;

            $fromDb = $class::find()->andWhere(['id' => array_keys($listIds)])->indexBy('id')->all();
            $result = [];
            if (!empty($fromDb)) {
                foreach ($listIds as $id => $score) {
                    $model = $fromDb[$id];
                    //add score to model
                    if ($model->hasAttribute('elasticScore')) {
                        $model->elasticScore = $score;
                    }
                    $result[] = $model;
                }
            }
        }

        return $result;
    }
}
