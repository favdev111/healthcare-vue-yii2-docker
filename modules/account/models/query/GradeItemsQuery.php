<?php

namespace modules\account\models\query;

use common\components\ActiveQuery;
use yii\db\ActiveQueryInterface;

/**
 * This is the ActiveQuery class for [[\modules\account\models\GradeItem]].
 *
 * @see \modules\account\models\GradeItem
 */
class GradeItemsQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return \modules\account\models\GradeItem[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    public function active(): self
    {
        return $this->andWhere(['deletedAt' => null]);
    }

    public function deleted(): self
    {
        return $this->andWhere(['not', 'deletedAt', null]);
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\GradeItem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
