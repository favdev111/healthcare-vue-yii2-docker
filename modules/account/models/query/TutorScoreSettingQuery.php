<?php

namespace modules\account\models\query;

use common\components\ActiveQuery;
use modules\account\models\TutorScoreSettings;

class TutorScoreSettingQuery extends ActiveQuery
{
    public function mostRecentActivity()
    {
        return $this->andWhere(['type' => TutorScoreSettings::TYPE_RECENT_ACTIVITY]);
    }

    public function distanceScore()
    {
        return $this->andWhere(['type' => TutorScoreSettings::TYPE_DISTANCE_SCORE]);
    }

    public function availability()
    {
        return $this->andWhere(['type' => TutorScoreSettings::TYPE_AVAILABILITY_SCORE]);
    }

    public function hoursPerRelation()
    {
        return $this->andWhere(['type' => TutorScoreSettings::TYPE_HOURS_PER_RELATION_SCORE]);
    }

    public function byType(int $type)
    {
        return $this->andWhere(['type' => $type]);
    }

    public function rematchesPerMatch()
    {
        return $this->andWhere(['type' => TutorScoreSettings::TYPE_REMATCHES_PER_MATCH_SCORE]);
    }

    public function refundsPerMatch()
    {
        return $this->andWhere(['type' => TutorScoreSettings::TYPE_REFUNDS_PER_MATCH_SCORE]);
    }

    public function hoursPerSubject()
    {
        return $this->andWhere(['type' => TutorScoreSettings::TYPE_HOURS_PER_SUBJECT_SCORE]);
    }
}
