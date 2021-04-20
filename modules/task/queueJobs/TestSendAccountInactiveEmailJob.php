<?php

namespace modules\task\queueJobs;

use common\helpers\QueueHelper;
use modules\account\models\Account;

class TestSendAccountInactiveEmailJob extends SendAccountInactiveEmailJob
{
    public $emails;
    public $isOneJob = false;

    public function getSearchCondition(): \modules\account\models\query\AccountQuery
    {
        return parent::getSearchCondition()->byEmail($this->emails);
    }

    public function createNextJob()
    {
        if (!$this->isOneJob) {
            if (!$this->isLastRound()) {
                $nextRound = $this->round + 1;
                //delay - 2 days
                QueueHelper::testAccountInactiveEmail(
                    $nextRound,
                    $this->isOneJob,
                    $this->emails,
                    60 * 60 * 24 * 2
                );
            }
        }
    }
}
