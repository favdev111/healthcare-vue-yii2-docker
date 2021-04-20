<?php

namespace common\components;

use common\helpers\QueueHelper;
use modules\chat\models\ChatMessage;
use yii\base\Component;
use modules\account\models\Account;
use modules\account\models\Job;

class PushMessage extends Component
{
    public function newJobOfferFromCompany(
        Job $job,
        Account $tutor
    ) {
        QueueHelper::pushMessage(
            [$tutor->id],
            'job-offer--new',
            'You\'ve just received a new offer from ' . $job->account->profile->showName . ' for ' . $job->getName(),
            [
                'jobId' => $job->id,
            ]
        );
    }

    public function newChatMessage(
        ChatMessage $messageModel,
        Account $accountFrom,
        Account $accountTo
    ) {
        $firstName = $accountFrom->profile->firstName;
        $message = "New message from ${firstName}: ";
        if (empty($message->chatAttachmentUid)) {
            $shortMessage = StringHelper::truncateWords(strip_tags($messageModel->message), 100);
            $message .= $shortMessage;
        } else {
            $message .= 'Attachment';
        }

        QueueHelper::pushMessage(
            [$accountTo->id],
            'chat--new-message',
            $message,
            [
                'dialogId' => $messageModel->chat_dialog_id,
            ]
        );
    }

    public function newJobPosted(
        Job $job,
        Account $account
    ) {
        $subject = $job->getSubjectsOrCategories()[0];
        $in = $job->isOnline ? '' : 'in ';
        $message = 'New ' . $subject->name . " tutoring opportunity $in" . $job->getCityName() .  '. Tap to apply.';

        QueueHelper::pushMessage(
            [$account->id],
            'job--new-posted',
            $message,
            [
                'jobId' => $job->id,
            ]
        );
    }
}
