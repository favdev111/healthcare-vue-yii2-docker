<?php

namespace modules\account\models\api2\forms;

use modules\account\models\api2\Account;
use modules\account\models\api2\Profile;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;

/**
 * Class UpdateSpecialist
 * @package modules\account\models\api2\forms
 */
class UpdateSpecialist extends SignUpSpecialist
{
    /**
     * @var string
     */
    public $gender;
    /**
     * @var Account
     */
    protected $account;

    /**
     * UpdateSpecialist constructor.
     * @param Account $account
     * @param Account|array $config
     */
    public function __construct(Account $account, $config = [])
    {
        if (!$account->isSpecialist()) {
            throw new InvalidArgumentException('Account must have a specialist role');
        }
        $this->account = $account;
        parent::__construct($config);
    }

    /**
     * @return string[]
     */
    public function attributes()
    {
        return [
            'gender',
            'phoneNumber',
            'firstName',
            'lastName',
            'dateOfBirth',
        ];
    }

    /**
     * @return string[][]
     */
    public function scenarios()
    {
        return [self::SCENARIO_DEFAULT => $this->attributes()];
    }

    /**
     * @return array|void
     */
    public function rules()
    {
        $rules = [
            ['gender', 'required'],
            ['gender', 'in', 'range' => array_keys(Profile::getGenderArray())],
        ];

        return array_merge($rules, parent::rules());
    }

    /**
     * @return Account|null
     * @throws ErrorException
     */
    public function update(): ?Account
    {
        if (!$this->validate()) {
            return null;
        }

        $this->buildProfile($this->account->profile);

        $this->account->refresh();
        return $this->account;
    }

    /**
     * @param Profile $profile
     * @return Profile
     * @throws ErrorException
     */
    protected function buildProfile(Profile $profile): Profile
    {
        $profile->gender = $this->gender;
        $profile->phoneNumber = $this->phoneNumber;
        $profile->firstName = $this->firstName;
        $profile->lastName = $this->lastName;
        $profile->dateOfBirth = $this->dateOfBirth;

        if (!$profile->save()) {
            throw new ErrorException('Profile was not saved');
        }

        return $profile;
    }
}
