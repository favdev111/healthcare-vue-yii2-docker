<?php

namespace modules\account\helpers;

use common\models\ProcessedEvent;
use modules\account\models\api\AccountClient;
use modules\account\models\api\ClientRefund;
use modules\account\models\api\ClientRematch;
use modules\account\models\Job;
use modules\account\models\PhoneValidation;
use modules\payment\models\Transaction;

class ConstantsHelper
{
    public const GENDER_MALE = 'M';
    public const GENDER_FEMALE = 'F';
    public const GENDER_NON_BINARY = 'N';
    public const GENDER_OTHER = 'O';

    public const MARITAL_STATUS__SINGLE = 1;
    public const MARITAL_STATUS__MARRIED = 2;
    public const MARITAL_STATUS__DIVORCED = 3;
    public const MARITAL_STATUS__OTHER = 99;

    public const EDUCATION_LEVEL__HIGH_SCHOOL = 1;
    public const EDUCATION_LEVEL__GRADUATE = 2;
    public const EDUCATION_LEVEL__POST_GRADUATE = 3;

    public const LESSON_STATUS__REQUIRES_TUTOR = 1;
    public const LESSON_STATUS__TUTOR_ASSIGNED = 2;
    public const LESSON_STATUS__ACTIVE_LESSON = 3;

    public const PAYMENT_STATUS__NO_PAYMENT_ADDED = 1;
    public const PAYMENT_STATUS__PAYMENT_ISSUES = 2;
    public const PAYMENT_STATUS__PAYMENT_ADDED = 3;

    public const HEALTH_PROFILE_RELATIONSHIP_PARENT = 1;
    public const HEALTH_PROFILE_RELATIONSHIP_GRANDPARENT = 2;
    public const HEALTH_PROFILE_RELATIONSHIP_UNCLE = 3;
    public const HEALTH_PROFILE_RELATIONSHIP_AUNT = 4;
    public const HEALTH_PROFILE_RELATIONSHIP_GUARDIAN = 5;

    const HEALTH_PROFILE_RELATIONSHIPS = [
        self::HEALTH_PROFILE_RELATIONSHIP_PARENT => 'Parent',
        self::HEALTH_PROFILE_RELATIONSHIP_GRANDPARENT => 'Grandparent',
        self::HEALTH_PROFILE_RELATIONSHIP_UNCLE => 'Uncle',
        self::HEALTH_PROFILE_RELATIONSHIP_AUNT => 'Aunt',
        self::HEALTH_PROFILE_RELATIONSHIP_GUARDIAN => 'Guardian',
    ];

    const MATERIAL_STATUS = [
        self::MARITAL_STATUS__SINGLE => 'Single',
        self::MARITAL_STATUS__MARRIED => 'Married',
        self::MARITAL_STATUS__DIVORCED => 'Divorced',
        self::MARITAL_STATUS__OTHER => 'Other',
    ];

    const EDUCATION_LEVEL = [
        self::EDUCATION_LEVEL__HIGH_SCHOOL => 'High school',
        self::EDUCATION_LEVEL__GRADUATE => 'Graduate',
        self::EDUCATION_LEVEL__POST_GRADUATE => 'Post graduate',
    ];

    public static function lessonOccur()
    {
        return [
            Job::LESSON_OCCUR_AT_HOME => 'At home',
            Job::LESSON_OCCUR_PUBLIC_LOCATION => 'Library / public place',
            Job::LESSON_OCCUR_TUTORS_LOCATION => 'Tutor\'s location',
            Job::LESSON_OCCUR_ONLINE => 'Online'
        ];
    }

    private static $gradeArray = [
        1 => 'Elementary',
        2 => 'Middle school',
        3 => 'High school',
        4 => 'College',
    ];

    public static function schoolGradeLevel()
    {
        return static::$gradeArray + [5 => 'Adult'];
    }

    public static function gender()
    {
        return [
            static::GENDER_MALE => 'Male',
            static::GENDER_FEMALE => 'Female',
            static::GENDER_NON_BINARY => 'Non-binary',
        ];
    }

    public static function genderPatient()
    {
        return [
            static::GENDER_MALE => 'Male',
            static::GENDER_FEMALE => 'Female',
            static::GENDER_NON_BINARY => 'Non-binary',
            static::GENDER_OTHER => 'Other',
        ];
    }

    public static function maritalStatus()
    {
        return static::MATERIAL_STATUS;
    }

    public static function educationLevel()
    {
        return static::EDUCATION_LEVEL;
    }

    /**
     * @return string[]
     */
    public static function relationship()
    {
        return static::HEALTH_PROFILE_RELATIONSHIPS;
    }

    public static function genderJob()
    {
        return array_merge(
            self::gender(),
            [
                'B' => 'Both',
            ]
        );
    }

    public static function clientLessonStatus()
    {
        return [
            self::LESSON_STATUS__REQUIRES_TUTOR => 'Requires Tutor',
            self::LESSON_STATUS__TUTOR_ASSIGNED => 'Tutor Assigned',
            self::LESSON_STATUS__ACTIVE_LESSON => 'Active Lessons',
        ];
    }

    public static function clientPaymentStatus()
    {
        return [
            self::PAYMENT_STATUS__NO_PAYMENT_ADDED => 'No Payment Added',
            self::PAYMENT_STATUS__PAYMENT_ISSUES => 'Payment Issues',
            self::PAYMENT_STATUS__PAYMENT_ADDED => 'Payment Added',
        ];
    }

    public static function processedEvents()
    {
        return [
            ProcessedEvent::TYPE_TUTOR_NOT_APPLIED => 'Not applied',
            ProcessedEvent::TYPE_TUTOR_NOTIFIED_ABOUT_NEW_JOB => 'Notified about job',
            ProcessedEvent::TYPE_NEW_JOB_POSTED_NOTIFICATIONS_PROCESSED => 'New job posted notification processed',
        ];
    }

    public static function startLessonTime()
    {
        return [
            1 => 'Today',
            2 => 'Within a few days',
            3 => 'Within two weeks',
            4 => 'This month',
        ];
    }

    public static function transactionObjectType()
    {
        return [
            Transaction::TYPE_ACCOUNT => 'Account',
            Transaction::TYPE_LESSON => 'Lesson',
            Transaction::TYPE_BACKGROUNDCHECKREPORT => 'Background Report',
            Transaction::TYPE_CLIENT_BALANCE_AUTO => 'Automatically',
            Transaction::TYPE_CLIENT_BALANCE_MANUAL_CHARGE => 'Manually',
            Transaction::TYPE_CLIENT_BALANCE_POST_PAYMENT => "Post Payment",
            Transaction::TYPE_LESSON_BATCH_PAYMENT => "Lesson (Batch Payment)",
            Transaction::TYPE_COMPANY_GROUP_PAYMENT => "Tutor Payouts",
        ];
    }


    public static function transactionStatus()
    {
        return [
            Transaction::STATUS_NEW => 'New',
            Transaction::STATUS_SUCCESS => 'Success',
            Transaction::STATUS_ERROR => 'Error',
            Transaction::STATUS_WAITING_FOR_APPROVE => 'Waiting Approval',
            Transaction::STATUS_REJECTED => 'Rejected',
            Transaction::STATUS_PENDING => 'Pending',
        ];
    }

    public static function transactionTypes()
    {
        return Transaction::$types;
    }

    public static function clientFlags()
    {
        return AccountClient::$flagData;
    }

    public static function phoneNumberTypes(): array
    {
        return static::convertArray(PhoneValidation::$typeLabels, true);
    }

    /**
     * Convert array (key => value) to (id => key, label => value)
     *
     * @param array $array
     * @param bool $revertKeyValue
     * @return array
     */
    public static function convertArray(array $array, bool $revertKeyValue = false)
    {
        $newArray = [];

        foreach ($array as $k => $v) {
            $newArray[] = [
                'id' => $revertKeyValue ? $v : $k,
                'label' => $revertKeyValue ? $k : $v,
            ];
        }

        return $newArray;
    }

    /**
     * Prepare data for Refund or Rematch reasons arrays
     * @param string $class - \modules\account\models\api\ClientRefund or \modules\account\models\api\ClientRematch
     * @param array $constants
     * @return array
     */
    protected static function prepareReasonArray(string $class, array $constants): array
    {
        $reasons = [];
        foreach ($constants as $constant) {
            $reasons[] = [
                'id' => $constant,
                'description' => $class::$reasonDescription[$constant] ?? '',
                //can new item be created with this status
                'isActive' => $class::isReasonActive($constant)
            ];
        }

        return $reasons;
    }

    /**
     * Get array of reasons for client refunds
     * @return array
     */
    public static function getClientRefundsReasons(): array
    {
        $constants = [
            ClientRefund::REASON_OTHER,
            ClientRefund::REASON_UNSATISFIED,
            ClientRefund::REASON_TOO_LONG,
            ClientRefund::REASON_DOEST_NOT_WANT_ONLINE,
            ClientRefund::REASON_ATTENDANCE_ISSUE,
            ClientRefund::REASON_PERSONAL_ISSUES,
            ClientRefund::REASON_TUTOR_REFUSE,
            ClientRefund::REASON_UNAUTHORIZED_CHARGE,
            ClientRefund::REASON_UNUSED_TUTORING_HOURS,
            ClientRefund::REASON_BUYERS_REMORSE,
        ];

        return self::prepareReasonArray(ClientRefund::class, $constants);
    }

    /**
     * Get array of reasons for client Rematch
     * @return array
     */
    public static function getClientRematchReasons(): array
    {
        $constants = [
            ClientRematch::REASON_OTHER,
            ClientRematch::REASON_UNSATISFIED,
            ClientRematch::REASON_CAN_NOT_CONNECT,
            ClientRematch::REASON_DOEST_NOT_WANT_ONLINE,
            ClientRematch::REASON_STUDENT_CHANGED_PREFERENCES,
            ClientRematch::REASON_TUTOR_UNAVAILABLE,
            ClientRematch::REASON_PERSONAL_ISSUES,
            ClientRematch::REASON_REFUND_SAVED,
            ClientRematch::REASON_ATTENDANCE_ISSUE,
            ClientRematch::REASON_MEETING_FOR_REFUND,
            ClientRematch::REASON_TUTOR_DOES_NOT_WANT_TO_CONTINUE,
            ClientRematch::REASON_TUTOR_UNAVAILABLE_AFTER_30_DAYS,
        ];

        return self::prepareReasonArray(ClientRematch::class, $constants);
    }
}
