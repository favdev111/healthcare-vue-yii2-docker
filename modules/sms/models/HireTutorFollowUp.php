<?php

namespace modules\sms\models;

use common\helpers\SmsHelper;
use common\models\Sms;
use modules\account\models\Account;
use modules\account\models\JobHire;

class HireTutorFollowUp extends FollowUp
{
    const SMS_TYPE = Sms::TYPE_FOLLOW_UP_HIRE_TO_TUTOR;

    /**
     * @var JobHire $jobHire
     */
    public $jobHire;
    public function __construct(JobHire $jobHire)
    {
        parent::__construct([]);
        $this->jobHire = $jobHire;
        $this->extraData = ['jobHireId' => $jobHire->id];
    }

    //get tutor account
    public function getAccount(): Account
    {
        return $this->jobHire->tutor;
    }

    public function getData(): array
    {
        $tutorName = $this->account->profile->firstName;
        $clientName = $this->jobHire->job->account->profile->fullName();
        return array_merge([$tutorName, $clientName], parent::getData());
    }

    public function getComposedMessage()
    {
        list($tutorName, $clientName) = $this->getData();
        $messageParts = [
            'Hi ',
            '',
            ". Have you scheduled a session with ",
            '',
            '? Please reply back “YES” or “NO”.',
        ];
        $replacements = [
            $tutorName,
            $clientName,
        ];
        return SmsHelper::truncateMessage($messageParts, $replacements);
    }
}
