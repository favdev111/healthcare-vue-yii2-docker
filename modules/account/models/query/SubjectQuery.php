<?php

namespace modules\account\models\query;

use common\components\ActiveQuery;
use Yii;

/**
 * Class SubjectQuery
 * @package modules\account\models
 * @property string $tableName
 */
class SubjectQuery extends ActiveQuery
{

    /**
     * Add order by name
     * @param $sort
     * @return $this
     */
    public function addOrderByName($sort)
    {
        $this->orderBy([$this->tableName . '.name' => $sort]);
        return $this;
    }

    public function bySlug(string $value): self
    {
        return $this->andWhere(['slug' => $value]);
    }

    public function byName(string $value): self
    {
        return $this->andWhere(['name' => $value]);
    }
}
