<?php

namespace modules\account\models\forms;

use common\components\behaviors\ChildModelErrorsBehavior;
use common\components\validators\TwilioPhoneValidator;
use modules\account\components\ZipCodeValidator;
use modules\account\models\Account;
use modules\account\models\AccountEmail;
use modules\account\models\api\AccountPhone;
use modules\account\models\api\ProfileClient;
use modules\account\models\Rate;
use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Class BookTutorWizardForm
 * @method void collectErrors($model) - from ChildModelErrorsBehavior
 * @package modules\account\models\forms
 */
class BookTutorWizardForm extends Model
{
    const DEFAULT_PRICE_FOR_HOUR = 65;
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $email;
    public $schoolGradeLevelId;
    public $subjects;
    public $note;
    public $startDate;
    public $duration;
    public $timePreferences;
    public $hourlyRate;
    public $tutorBookingId;
    public $step;
    public $paymentAdd;
    public $tutorId;
    public $zipCode;
    public $gclid;
    public $isTermsSigned;

    //public static
    public static $allowedSteps = [1, 2, 3, 4, 5];

    //rate => hours
    public static $packages = [
        65 => [
            'hourlyRate' => self::DEFAULT_PRICE_FOR_HOUR,
            'hours' => 1,
            'discount' => 0
        ],
        50 => [
            'hourlyRate' => 50,
            'hours' => 3,
            'discount' => 45
        ],
        40 => [
            'hourlyRate' => 40,
            'hours' => 10,
            'discount' => 250
        ],
    ];

    public static function isStepAllowed(int $step): bool
    {
        return in_array($step, self::$allowedSteps);
    }

    //protected
    protected function validateModel(string $class, array $attributes, string $scenario = null)
    {
        $model = new $class();
        if (!($model instanceof Model)) {
            throw new InvalidArgumentException('Provided class should extends Model class');
        }

        $model->setAttributes($this->attributes, false);

        if ($scenario) {
            $model->setScenario($scenario);
        }

        $model->validate($attributes);
        if ($model->hasErrors()) {
            $this->collectErrors($model);
        }
    }

    //public
    public function setStepScenario(int $step): void
    {
        $this->setScenario('step_' . $step);
    }

    public function behaviors()
    {
        return [
            'ChildModelErrorsBehavior' => ChildModelErrorsBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [
                [
                    'firstName',
                    'lastName',
                    'phoneNumber',
                    'tutorId',
                    'email',
                    'gclid',
                ],
                'safe',
                'on' => ['step_1']
            ],
            [
                [
                    'schoolGradeLevelId',
                    'subjects',
                    'note',
                ],
                'safe',
                'on' => ['step_2']
            ],
            [
                [
                    'startDate',
                    'timePreferences',
                ],
                'safe',
                'on' => ['step_3']
            ],
            [
                [
                    'hourlyRate',
                ],
                'safe',
                'on' => ['step_4']
            ],
            [
                [
                    'paymentAdd',
                ],
                'safe',
                'on' => ['step_5']
            ],
            [
                ['tutorBookingId', 'tutorId'], 'integer'
            ],
            [
                'tutorId',
                'required',
                'on' => 'step_1'
            ],
            [
                ['tutorBookingId'],
                'required',
                'on' => ['step_2','step_3','step_4','step_5']
            ],
            [
                'firstName',
                function () {
                    $this->validateModel(ProfileClientForm::class, ['firstName', 'lastName'], 'create');
                },
                'skipOnEmpty' => false,
                'on' => ['step_1']
            ],
            [
                'email',
                function () {
                    $this->validateModel(AccountEmail::class, ['email'], 'create');
                },
                'skipOnEmpty' => false,
                'on' => ['step_1']
            ],
            [
                'phoneNumber',
                function () {
                    $this->validateModel(AccountPhone::class, ['phoneNumber']);
                },
                'skipOnEmpty' => false,
                'on' => ['step_1']
            ],
            [
                'subjects',
                function () {
                    $this->validateModel(
                        ProfileClientForm::class,
                        ['subjects'],
                        'create'
                    );
                },
                'skipOnEmpty' => false,
                'on' => ['step_2']
            ],
            [
                'startDate',
                'required',
                'on' => ['step_3']
            ],
            [
                'startDate',
                function () {
                    $this->validateModel(
                        ProfileClientForm::class,
                        ['startDate'],
                        'create'
                    );
                },
                'skipOnEmpty' => false,
                'on' => ['step_3']
            ],
            [
                ['schoolGradeLevelId'],
                'required',
                'on' => ['step_2']
            ],
            [
                ['schoolGradeLevelId'],
                'in',
                'range' => array_keys(ProfileClient::getSchoolGradeLevelArray()),
                'on' => ['step_2']
            ],
            [
                'note',
                'string',
                'on' => ['step_2']
            ],
            [
                ['timePreferences'],
                'required',
                'on' => ['step_3']
            ],
            [
                'timePreferences',
                'integer',
                'on' => ['step_3']
            ],
            [
                'hourlyRate',
                function () {
                    $this->validateModel(
                        Rate::class,
                        ['hourlyRate'],
                        'setRate'
                    );
                },
                'skipOnEmpty' => false,
                'on' => ['step_4']
            ],
            [
                'paymentAdd',
                function () {
                    $this->validateModel(
                        ProfileClientForm::class,
                        ['paymentAdd'],
                        'book'
                    );
                },
                'skipOnEmpty' => false,
                'on' => ['step_5']
            ],
            [
                ['paymentAdd', 'isTermsSigned'],
                'required',
                'on' => ['step_5']
            ],
            [
                'isTermsSigned',
                'in',
                'range' => [true]
            ],
            [
                ['zipCode'],
                ZipCodeValidator::class,
                'on' => ['step_1'],
            ],
        ];
    }
}
