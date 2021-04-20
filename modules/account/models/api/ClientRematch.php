<?php

namespace modules\account\models\api;

use common\components\Formatter;
use modules\account\helpers\Timezone;

class ClientRematch extends AccountReturn
{
    protected static $newFlagColor = AccountClient::FLAG_RED;

    public static $changeLogDescription = 'Client rematch.';

    //reasons
    const REASON_OTHER = 1;
    const REASON_UNSATISFIED = 2;
    const REASON_CAN_NOT_CONNECT = 3;
    const REASON_DOEST_NOT_WANT_ONLINE = 4;
    const REASON_ATTENDANCE_ISSUE = 5;
    const REASON_PERSONAL_ISSUES = 6;
    const REASON_STUDENT_CHANGED_PREFERENCES = 7;
    const REASON_TUTOR_UNAVAILABLE = 8;
    const REASON_REFUND_SAVED = 9;
    const REASON_MEETING_FOR_REFUND = 10;
    const REASON_TUTOR_DOES_NOT_WANT_TO_CONTINUE = 11;
    const REASON_TUTOR_UNAVAILABLE_AFTER_30_DAYS = 12;


    public static $reasonDescription = [
        self::REASON_OTHER => "Other",
        self::REASON_UNSATISFIED => 'Unsatisfied with tutor/session(s)',
        self::REASON_CAN_NOT_CONNECT => 'Can’t connect',
        self::REASON_DOEST_NOT_WANT_ONLINE => 'Doesn\'t want online',
        self::REASON_STUDENT_CHANGED_PREFERENCES => 'Student changed preferences (tutor or schedule)',
        self::REASON_TUTOR_UNAVAILABLE => 'Tutor no longer available (within 30 days of hire)',
        self::REASON_TUTOR_UNAVAILABLE_AFTER_30_DAYS => 'Tutor no longer available (after 30 days of hire)',
        self::REASON_PERSONAL_ISSUES => 'Personal issues',
        self::REASON_REFUND_SAVED => 'Refund request saved',
        self::REASON_ATTENDANCE_ISSUE => 'Attendance issue(s)',
        self::REASON_MEETING_FOR_REFUND => 'Meeting 2nd tutor for refund',
        self::REASON_TUTOR_DOES_NOT_WANT_TO_CONTINUE => 'Tutor doesn\'t want to meet/continue',
    ];

    public static $activeReasons = [
        self::REASON_UNSATISFIED,
        self::REASON_CAN_NOT_CONNECT,
        self::REASON_DOEST_NOT_WANT_ONLINE,
        self::REASON_STUDENT_CHANGED_PREFERENCES,
        self::REASON_TUTOR_UNAVAILABLE,
        self::REASON_PERSONAL_ISSUES,
        self::REASON_ATTENDANCE_ISSUE,
        self::REASON_TUTOR_DOES_NOT_WANT_TO_CONTINUE,
        self::REASON_TUTOR_UNAVAILABLE_AFTER_30_DAYS,
    ];

    public static $reasonsNotAffectedToStatistic = [
        self::REASON_DOEST_NOT_WANT_ONLINE,
        self::REASON_STUDENT_CHANGED_PREFERENCES,
        self::REASON_PERSONAL_ISSUES,
    ];

    public function fields()
    {
        return [
            'id',
            'type',
            'accountId',
            'reasonCode',
            'description',
            'startDate',
            'jobHiresIds',
            'createdBy',
            'createdAt',
        ];
    }

    public static function find()
    {
        return parent::find()->rematch();
    }

    public function getNote(): string
    {
        return "Rematch. Reason: " . parent::getNote() . "\n" . $this->description;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->setClientStartDate();
            $this->processJobHires();
        }
    }
}
