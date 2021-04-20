<?php

namespace modules\task\queueJobs;

use modules\account\helpers\TutorHelper;
use modules\account\models\Account;
use modules\task\components\RetryableJob;

class CalculateTutorProfileUniqueWordsJob extends RetryableJob
{
    public $accountId;

    public function execute($queue)
    {
        $account = Account::findOneWithoutRestrictions($this->accountId);
        if (!$account || !$account->isTutor()) {
            return;
        }

        $account->updateAttributes([
            'profileUniqueWordsCount' => TutorHelper::calculateTutorProfileUniqueWords($account),
        ]);
    }
}
