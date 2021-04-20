<?php

namespace modules\account\models\query;

class JobHireQuery extends \common\components\ActiveQuery
{
    public function activeOrWasActive(): self
    {
        return $this->andWhere(
            [
                'or',
                ['status' => \modules\account\models\JobHire::STATUS_HIRED],
                [
                    'and',
                    ['status' => \modules\account\models\JobHire::STATUS_DECLINED_BY_COMPANY],
                    ['not', ['tutoringHours' => 0]]
                ],
                ['status' => \modules\account\models\JobHire::STATUS_CLOSED_BY_COMPANY]
            ]
        );
    }

    public function byTutor(int $id): self
    {
        return $this->andWhere(['tutorId' => $id]);
    }
}
