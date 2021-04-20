<?php

namespace modules\account\models\query;

/**
 * This is the ActiveQuery class for [[\modules\account\models\AutomatchHistory]].
 *
 * @see \modules\account\models\AutomatchHistory
 */
class AutomatchHistoryQuery extends \yii\db\ActiveQuery
{
    public function byJob($id): self
    {
        return $this->andWhere(['jobId' => $id]);
    }

    public function byMatchedTutor($id): self
    {
        return $this->andWhere(['matchedTutor' => $id]);
    }
}
