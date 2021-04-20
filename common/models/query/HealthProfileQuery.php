<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\query\HealthProfileQuery]].
 *
 * @see \modules\account\models\GradeItem
 */
class HealthProfileQuery extends \yii\db\ActiveQuery
{
    public function active(): self
    {
        return $this->andWhere(['deletedAt' => null]);
    }

    public function deleted(): self
    {
        return $this->andWhere(['not', 'deletedAt', null]);
    }
}
