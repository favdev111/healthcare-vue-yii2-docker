<?php

namespace modules\account\models\forms\professional\role;

use api2\helpers\ProfessionalType;
use backend\models\CompositeForm;
use common\components\validators\NPIValidator;
use modules\account\models\Account;
use modules\account\models\ar\AccountLicenceState;
use modules\account\models\ar\AccountTelehealthState;
use modules\account\models\ar\State;
use modules\account\models\Profile;
use Yii;
use yii\base\ErrorException;
use yii\db\conditions\AndCondition;
use yii\helpers\ArrayHelper;

/**
 * Class ProfessionalRoleForm
 * @package modules\account\models\forms\professional\role
 *
 * @property LicenseStateForm[] $licenceStateForms
 * @property-read null|array $telehealthStatesData
 * @property ProfessionalRoleOption $option
 */
class ProfessionalRoleForm extends CompositeForm
{
    /**
     * @var string
     */
    public $professionalTypeId;
    /**
     * @var string
     */
    public $doctorTypeId;
    /**
     * @var string
     */
    public $npiNumber;
    /**
     * @var string
     */
    public $yearsOfExperience;
    /**
     * @var boolean|string
     */
    public $hasDisciplinaryAction;
    /**
     * @var string
     */
    public $disciplinaryActionText;
    /**
     * @var string
     */
    public $telehealthStates = [];
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var ProfessionalRoleOption
     */
    protected ProfessionalRoleOption $option;

    /**
     * ProfessionalRoleForm constructor.
     * @param Account $account
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(Account $account, $config = [])
    {
        $this->account = $account;
        if (!isset($this->option)) {
            $this->option = Yii::createObject(ProfessionalRoleOption::class);
        }
        $this->account = $account;
        parent::__construct($config);
    }

    public function init(): void
    {
        $profile = $this->account->profile;

        $this->professionalTypeId = $profile->professionalTypeId;
        $this->doctorTypeId = $profile->doctorTypeId;
        $this->npiNumber = $profile->npiNumber;
        $this->yearsOfExperience = $profile->yearsOfExperience;
        $this->hasDisciplinaryAction = $profile->hasDisciplinaryAction;
        $this->disciplinaryActionText = $profile->disciplinaryActionText;
        $this->telehealthStates = $this->account
            ->getTelehealthStates()
            ->select('stateId')
            ->column();

        $this->initLicenceStates();
    }

    protected function initLicenceStates()
    {
        $licenceStatesForms = self::createMultiple(LicenseStateForm::class, [], []);
        if (!$licenceStatesForms) {
            foreach ($this->account->licenceStates as $licenceState) {
                $licenceStatesForms[] = Yii::createObject([
                    'class' => LicenseStateForm::class,
                    'id' => $licenceState->id,
                    'license' => $licenceState->licence,
                    'stateId' => $licenceState->stateId,
                ]);
            }

            if (!$licenceStatesForms) {
                $licenceStatesForms[] = Yii::createObject([
                    'class' => LicenseStateForm::class,
                ]);
            }
        }
        $this->licenceStateForms = $licenceStatesForms;
    }

    /**
     * @return array|null
     */
    public function getTelehealthStatesData(): ?array
    {
        return State::find()->indexBy('id')
            ->select('name')
            ->andWhere(['id' => $this->telehealthStates])
            ->column();
    }

    public function rules()
    {
        return [
            ['professionalTypeId', 'in', 'range' => array_keys($this->option->professionalTypes)],
            ['yearsOfExperience', 'number', 'min' => 1, 'max' => 100],
            [
                'doctorTypeId',
                'in',
                'range' => array_keys($this->option->doctorTypes),
                'skipOnEmpty' => false,
                'when' => function () {
                    return $this->professionalTypeId == ProfessionalType::DOCTOR;
                }
            ],
            [
                'npiNumber',
                NPIValidator::class,
                'skipOnEmpty' => false,
                'when' => function () {
                    return $this->professionalTypeId == ProfessionalType::DOCTOR;
                }
            ],
            ['telehealthStates', 'telehealthStatesValidate', 'skipOnEmpty' => true],
            [
                'telehealthStates',
                'filter',
                'filter' => function ($value) {
                    if (empty($value)) {
                        return [];
                    }
                    return $value;
                }
            ],
            ['hasDisciplinaryAction', 'boolean'],
            [
                'hasDisciplinaryAction',
                'filter',
                'filter' => function ($value) {
                    return (bool)(int)$value;
                }
            ],
            [
                'disciplinaryActionText',
                'string',
                'skipOnEmpty' => false,
                'min' => 1,
                'when' => function () {
                    return $this->hasDisciplinaryAction;
                }
            ],
        ];
    }

    /**
     * @param $attribute
     * @return void|null
     */
    public function telehealthStatesValidate($attribute)
    {
        $message = Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]);
        if (!is_array($this->telehealthStates)) {
            $this->addError($attribute, $message);
            return;
        }

        foreach ($this->telehealthStates as $telehealthState) {
            $stateExist = State::find()
                ->where(['id' => $telehealthState])
                ->exists();

            if (!$stateExist) {
                $this->addError($attribute, $message);
                return null;
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'professionalTypeId' => 'Professional type',
            'yearsOfExperience' => 'Years of experience',
            'doctorTypeId' => 'Doctor type',
            'npiNumber' => 'NPI #',
        ];
    }

    /**
     * @return ProfessionalRoleOption
     */
    public function getOption(): ProfessionalRoleOption
    {
        return $this->option;
    }

    /**
     * @return Account|null
     * @throws ErrorException
     * @throws \yii\db\Exception
     */
    public function save(): ?Account
    {
        if (!$this->validate()) {
            return null;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->buildTelehealthStates($this->account, $this->telehealthStates);
            $this->buildLicenceStateForms($this->account, $this->licenceStateForms);
            $this->buildProfile($this->account->profile);

            $transaction->commit();
            return $this->account;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * @param Account $account
     * @param $telehealthStates
     * @return array
     * @throws ErrorException
     */
    protected function buildTelehealthStates(Account $account, $telehealthStates): array
    {
        $newTelehealthStates = [];

        foreach ($telehealthStates as $telehealthState) {
            $accountTelehealthState = AccountTelehealthState::find()
                ->where(['accountId' => $account->id])
                ->andWhere(['stateId' => $telehealthState])
                ->one();

            if ($accountTelehealthState) {
                $newTelehealthStates[] = $accountTelehealthState;
            } else {
                $newTelehealthStates[] = $this->buildTelehealthState($account, $telehealthState);
            }
        }

        $ids = ArrayHelper::getColumn($newTelehealthStates, 'id', []);
        AccountTelehealthState::deleteAll(new AndCondition([
            ['NOT IN', 'id', $ids],
            ['accountId' => $account->id]
        ]));

        return $newTelehealthStates;
    }

    /**
     * @param Account $account
     * @param array $licenceStateForms
     * @return array
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildLicenceStateForms(Account $account, array $licenceStateForms): array
    {
        $newLicenceStates = [];

        foreach ($licenceStateForms as $licenceStateForm) {
            if ($licenceStateForm->id) {
                $accountLicenceState = AccountLicenceState::findOne(['id' => $licenceStateForm->id]);
                if ($accountLicenceState) {
                    $newLicenceStates[] = $this->buildLicenceState($accountLicenceState, $account, $licenceStateForm);
                }
            } else {
                $newLicenceStates[] = $this->buildLicenceState(Yii::createObject(AccountLicenceState::class), $account, $licenceStateForm);
            }
        }

        if ($licenceStateForms) {
            $ids = ArrayHelper::getColumn($newLicenceStates, 'id', []);
            AccountLicenceState::deleteAll(new AndCondition([
                ['NOT IN', 'id', $ids],
                ['accountId' => $account->id]
            ]));
        }

        return $newLicenceStates;
    }

    /**
     * @param Account $account
     * @param $telehealthState
     * @return AccountTelehealthState
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildTelehealthState(Account $account, $telehealthState): AccountTelehealthState
    {
        $accountTelehealthState = \Yii::createObject(AccountTelehealthState::class);
        $accountTelehealthState->accountId = $account->id;
        $accountTelehealthState->stateId = $telehealthState;

        if (!$accountTelehealthState->save()) {
            throw new ErrorException('AccountTelehealthState was not saved');
        }

        return $accountTelehealthState;
    }

    /**
     * @param AccountLicenceState $accountLicenceState
     * @param Account $account
     * @param LicenseStateForm $form
     * @return AccountLicenceState
     * @throws ErrorException
     */
    protected function buildLicenceState(AccountLicenceState $accountLicenceState, Account $account, LicenseStateForm $form): AccountLicenceState
    {
        $accountLicenceState->accountId = $account->id;
        $accountLicenceState->licence = $form->license;
        $accountLicenceState->stateId = $form->stateId;

        if (!$accountLicenceState->save()) {
            throw new ErrorException('AccountLicenceState was not saved');
        }

        return $accountLicenceState;
    }

    /**
     * @param Profile $profile
     * @return Profile
     * @throws ErrorException
     */
    protected function buildProfile(Profile $profile): Profile
    {
        $profile->professionalTypeId = $this->professionalTypeId ?? $profile->professionalTypeId;
        $profile->yearsOfExperience = $this->yearsOfExperience ?? $profile->yearsOfExperience;
        $profile->hasDisciplinaryAction = $this->hasDisciplinaryAction ?? $profile->hasDisciplinaryAction;

        if ($profile->professionalTypeId == ProfessionalType::DOCTOR) {
            $profile->doctorTypeId = $this->doctorTypeId;
            $profile->npiNumber = $this->npiNumber;
        } else {
            $profile->doctorTypeId = null;
            $profile->npiNumber = null;
        }

        if ($profile->hasDisciplinaryAction) {
            $profile->disciplinaryActionText = $this->disciplinaryActionText;
        } else {
            $profile->disciplinaryActionText = null;
        }

        if (!$profile->save(false)) {
            throw new ErrorException('Profile was not saved');
        }
        return $profile;
    }

    /**
     * @return string[]
     */
    protected function internalForms()
    {
        return [
            'licenceStateForms',
        ];
    }
}
