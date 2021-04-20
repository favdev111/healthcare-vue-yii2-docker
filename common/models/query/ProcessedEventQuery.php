<?php

namespace common\models\query;

use common\components\ActiveQuery;
use common\models\ProcessedEvent;

class ProcessedEventQuery extends ActiveQuery
{
    public function job(int $jobId): self
    {
        return $this->andWhere(['jobId' => $jobId]);
    }

    public function type(int $type): self
    {
        return $this->andWhere(['type' => $type]);
    }

    public function accountId(int $id): self
    {
        return $this->andWhere(['accountId' => $id]);
    }

    public function tutorNotifiedAboutNewJob(): self
    {
        return $this->type(ProcessedEvent::TYPE_TUTOR_NOTIFIED_ABOUT_NEW_JOB);
    }

    public function newJobPostedNotificationProcessed(): self
    {
        return $this->type(ProcessedEvent::TYPE_NEW_JOB_POSTED_NOTIFICATIONS_PROCESSED);
    }
}
