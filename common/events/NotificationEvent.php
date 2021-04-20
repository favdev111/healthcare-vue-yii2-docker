<?php

namespace common\events;

use modules\account\models\Job;
use modules\account\models\JobApply;
use modules\account\models\JobOffer;
use yii\base\Event;

class NotificationEvent extends Event
{
    /** @var Job */
    public $job;
    public $review;
    public $lesson;
    public $owner;
    public $account;
    public $missingInformation;
    public $transaction;

    /** @var JobApply */
    public $jobApply;
    public $student;
    public $tutor;
    public $message;
    public $messageObject;
    public $messageModel;
    public $report;
    public $tutorPro;
    public $card;

    /** @var JobOffer */
    public $jobOffer;
    public $jobHire;
    public $shareContactInfo;
    public $checkSettings = true;
}
