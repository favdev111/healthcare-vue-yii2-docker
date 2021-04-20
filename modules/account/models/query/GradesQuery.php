<?php

namespace modules\account\models\query;

/**
 * This is the ActiveQuery class for [[\modules\account\models\Grade]].
 *
 * @see \modules\account\models\Grade
 */
class GradesQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return \modules\account\models\Grade[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    public function byCategory(string $category): self
    {
        return $this->andWhere(['category' => $category]);
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\Grade|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
