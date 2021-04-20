<?php

namespace modules\account\models\api;

use common\components\validators\JobHireClientValidator;
use modules\account\models\Team;

/**
 * Class ClientRefund
 * @package modules\account\models\api
 */
class ClientRefund extends AccountReturn
{
    protected static $newFlagColor = AccountClient::FLAG_ORANGE;
    //disable notification after creating RematchJobHire
    public $tutorsNotificationsEnabled = false;

    public static $changeLogDescription = 'Client refund.';

    //reasons
    const REASON_OTHER = 1;
    const REASON_UNSATISFIED = 2;
    const REASON_TOO_LONG = 3;
    const REASON_DOEST_NOT_WANT_ONLINE = 4;
    const REASON_ATTENDANCE_ISSUE = 5;
    const REASON_PERSONAL_ISSUES = 6;
    const REASON_TUTOR_REFUSE = 7;
    const REASON_UNAUTHORIZED_CHARGE = 8;
    const REASON_UNUSED_TUTORING_HOURS = 9;
    const REASON_BUYERS_REMORSE = 10;

    const SCENARIO_REFUND_AS_REMATCH = 'refundAsRematch';

    public static $reasonDescription = [
        self::REASON_OTHER => "Other",
        self::REASON_UNSATISFIED => 'Unsatisfied with tutor/session(s)',
        self::REASON_TOO_LONG => 'Taking too long',
        self::REASON_DOEST_NOT_WANT_ONLINE => 'Doesn\'t want online',
        self::REASON_ATTENDANCE_ISSUE => 'Tutor attendance issue',
        self::REASON_PERSONAL_ISSUES => 'Personal issues',
        self::REASON_TUTOR_REFUSE => 'Tutor refuses to meet/continue',
        self::REASON_UNAUTHORIZED_CHARGE => 'Unauthorized charge',
        self::REASON_UNUSED_TUTORING_HOURS => 'Unused tutoring hours',
        self::REASON_BUYERS_REMORSE => 'Buyers remorse (within 48hrs of sign up)',
    ];

    public static $activeReasons = [
        self::REASON_UNSATISFIED,
        self::REASON_TOO_LONG,
        self::REASON_ATTENDANCE_ISSUE,
        self::REASON_PERSONAL_ISSUES,
        self::REASON_PERSONAL_ISSUES,
        self::REASON_TUTOR_REFUSE,
        self::REASON_UNAUTHORIZED_CHARGE,
        self::REASON_UNUSED_TUTORING_HOURS,
        self::REASON_BUYERS_REMORSE,
    ];

    public function scenarios()
    {
        $fields = ['type', 'accountId', 'reasonCode', 'description', 'jobHireId'];
        return array_merge(parent::scenarios(), [
            static::SCENARIO_REFUND_AS_REMATCH => array_merge($fields, ['jobHiresIds', 'startDate']),
            static::SCENARIO_DEFAULT => array_merge($fields, ['employeeId'])
        ]);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                ['employeeId'],
                'required'
            ],
            [
                ['employeeId'],
                'integer'
            ],
            [['jobHireId'], JobHireClientValidator::class],
            [
                'employeeId',
                function () {
                    $account = Account::findOne($this->employeeId);
                    if (empty($account)) {
                        $this->addError('employeeId', 'Employee account not found');
                        return;
                    }
                    if (
                        !$account->isCrmAdmin()
                        && !($account->accountTeam->teamId == Team::OPS_TEAM_ID)
                    ) {
                        $this->addError('employeeId', 'Invalid employee selected');
                    }
                }
            ]
        ]);
    }

    public function fields()
    {
        return [
            'id',
            'type',
            'accountId',
            'reasonCode',
            'startDate',
            'jobHiresIds',
            'description',
            'employeeId',
            'createdBy',
            'createdAt',
            'jobHireId',
        ];
    }

    public static function find()
    {
        return parent::find()->refunds();
    }

    public function isTypeUnsatisfied(): bool
    {
        return static::REASON_UNSATISFIED == $this->reasonCode;
    }

    public function afterSave($insert, $changedAttributes)
    {
        //https://heytutor.atlassian.net/browse/HT-877
        if ($this->isTypeUnsatisfied() && $insert) {
            $this->processJobHires();
            $this->setClientStartDate();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    protected function changeFlag(): bool
    {
        if (parent::changeFlag()) {
            //remove relations between client and employee
            $relations = \modules\account\models\EmployeeClient::find()
                ->andWhere(['clientId' => $this->accountId])
                ->all();
            foreach ($relations as $relation) {
                $relation->delete();
            }
            return true;
        }
        return false;
    }

    public function getNote(): string
    {
        return "Refund request. Reason: " . parent::getNote() . "\n" . $this->description;
    }
}
