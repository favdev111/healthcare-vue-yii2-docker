<?php

namespace modules\task\queueJobs;

use common\models\Lead;
use modules\account\models\Account;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use modules\account\models\TutorBooking;
use modules\task\components\RetryableJob;
use yii\base\Exception;

class CheckBookingJob extends RetryableJob
{

    public $bookingId;
    public function execute($queue)
    {
        \Yii::info('Create lead from booking:' . $this->bookingId, 'lead');
        try {
            if (empty($this->bookingId)) {
                throw new Exception('Booking id is empty');
            }

            $booking = TutorBooking::findOne($this->bookingId);
            if (empty($booking)) {
                throw new Exception("Booking with id {$this->bookingId} hasn\'t been found.");
            }

            $sendLead = false;

            //send lead if account doesn't exists or exists but there is no payment method
            if ($booking->accountId) {
                $account = Account::findOne($booking->accountId);
                if (empty($account)) {
                    throw new Exception("Account hasn\'t been found.");
                }

                if (
                    ($booking->isAccountAlreadyExists && ($booking->step != 5))
                    || empty($account->cardInfo)
                ) {
                    $sendLead = true;
                }
            } else {
                //new account with not full list of steps
                $sendLead = true;
            }
            if ($sendLead) {
                $subjectOrCategory = SubjectOrCategory::findById($booking->subjects);
                $subjectName = $subjectOrCategory->getName();
                $lead = new Lead(
                    [
                        'firstName' => $booking->firstName . ' ' . $booking->lastName,
                        'subject' => $subjectName,
                        'subjectId' => (int)$booking->subjects,
                        'isCategory' => $subjectOrCategory->isCategory(),
                        'backendType' => Lead::BACKEND_TYPE_SALESFORCE,
                        'phone' => $booking->phoneNumber,
                        'email' => $booking->email,
                        'description' => $booking->fullNote,
                        'zipCode' => $booking->zipCode,
                        'source' => 'Book tutor wizard',
                        'clickId' => $booking->gclid,
                        'advertisingChannel' => $booking->advertisingChannel['name'],
                    ]
                );
                if (!$lead->save()) {
                    \Yii::error('Error during sending lead: ' . json_encode($lead->getErrors()), 'lead');
                    return false;
                }
                \Yii::info("Lead with id {$lead->id} has been created.", 'lead');
            } else {
                \Yii::info('Lead has not been sent.', 'lead');
            }
            return true;
        } catch (\Throwable $exception) {
            \Yii::error(
                'Error during sending lead: '
                . $exception->getMessage() . "\n" . $exception->getTraceAsString(),
                'lead'
            );
            return false;
        }
    }
}
