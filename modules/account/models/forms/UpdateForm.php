<?php

namespace modules\account\models\forms;

use backend\models\BaseForm;
use common\components\validators\GooglePlaceValidator;
use common\components\validators\NameStringValidator;
use common\models\GooglePlace;
use DateTime;
use modules\account\components\ZipCodeValidator;
use common\helpers\AccountStatusHelper;
use modules\account\models\Account;
use modules\account\models\Profile;
use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class UpdateForm
 *
 * @property-read array $statuses
 * @property-read Account $account
 * @property-read null|\common\models\GooglePlace $googlePlace
 * @property-read AccountOption $option
 */
class UpdateForm extends BaseForm
{
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var int
     */
    public $statusId;
    /**
     * @var string
     */
    public $genderId;
    /**
     * @var string
     */
    public $zipCode;
    /**
     * @var string
     */
    public $dateOfBirth;
    /**
     * @var boolean
     */
    public $searchHide;
    /**
     * @var boolean
     */
    public $publicHide;
    /**
     * @var string
     */
    public $phoneNumber;
    /**
     * @var string
     */
    public $placeId;
    /**
     * @var string
     */
    public $address;
    /**
     * @var Account
     */
    protected $account;
    /**
     * @var AccountOption
     */
    protected AccountOption $option;

    /**
     * ProfessionalUpdateForm constructor.
     * @param Account $account
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(Account $account, $config = [])
    {
        $this->account = $account;
        if (!isset($this->option)) {
            $this->option = Yii::createObject(AccountOption::class);
        }
        $this->account = $account;
        parent::__construct($config);
    }

    public function init(): void
    {
        if ($dateOfBirth = $this->account->profile->dateOfBirth) {
            $this->dateOfBirth = DateTime::createFromFormat('Y-m-d', $dateOfBirth)->format('m/d/Y');
            ;
        }
        $this->placeId = ArrayHelper::getValue($this->account->profile->googlePlace, 'placeId');
        $this->address = $this->account->profile->address;
        $this->genderId = $this->account->profile->gender;
        $this->firstName = $this->account->profile->firstName;
        $this->lastName = $this->account->profile->lastName;
        $this->zipCode = $this->account->profile->zipCode;
        $this->statusId = $this->account->status;
        $this->searchHide = $this->account->searchHide;
        $this->publicHide = $this->account->hideProfile;
        $this->phoneNumber = $this->account->profile->phoneNumber;
    }

    /**
     * @return AccountOption
     */
    public function getOption(): AccountOption
    {
        return $this->option;
    }

    /**
     * @return GooglePlace|null
     */
    public function getGooglePlace(): ?GooglePlace
    {
        if (!$this->placeId) {
            return null;
        }
        return GooglePlace::findOne(['placeId' => $this->placeId]);
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function rules()
    {
        return [
            ['address', 'safe'],
            ['placeId', GooglePlaceValidator::class],
            ['dateOfBirth', 'date', 'format' => 'php:m/d/Y'],
            ['zipCode', ZipCodeValidator::class],
            ['phoneNumber', 'udokmeci\yii2PhoneValidator\PhoneValidator', 'country' => 'US', 'format' => false],
            [
                'statusId',
                'in',
                'range' => array_keys($this->option->statuses),
                'when' => function (self $model) {
                    return $model->statusId != $this->account->status;
                }
            ],
            ['genderId', 'in', 'range' => array_keys($this->option->gender)],
            [['firstName', 'lastName'], NameStringValidator::class],
            [['firstName', 'lastName'], 'required'],
            [['searchHide', 'publicHide'], 'boolean'],
        ];
    }

    /**
     * @return array|string[]
     */
    public function attributeLabels()
    {
        return [
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'statusId' => 'Status',
            'searchHide' => 'Hide from search',
            'publicHide' => 'Hide from public',
            'genderId' => 'Gender',
            'address' => 'Address',
            'placeId' => 'Address',
        ];
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
            $account = $this->buildAccount($this->account);
            $this->buildProfile($account->profile);
            $transaction->commit();
            return $account;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * @param Account $account
     * @return Account
     * @throws ErrorException
     */
    protected function buildAccount(Account $account): Account
    {
        $account->status = $this->statusId ?? $account->status;
        $account->hideProfile = $this->publicHide ?? $account->hideProfile;
        $account->searchHide = $this->searchHide ?? $account->searchHide;

        if (!$account->save(false)) {
            throw new ErrorException();
        }

        return $account;
    }

    /**
     * @param Profile $profile
     * @return Profile
     * @throws ErrorException
     */
    protected function buildProfile(Profile $profile): Profile
    {
        if ($this->dateOfBirth) {
            $dateOfBirth = DateTime::createFromFormat('m/d/Y', $this->dateOfBirth)->format('Y-m-d');
            $profile->dateOfBirth = $dateOfBirth;
        }

        $profile->placeId = $this->placeId ?? $profile->placeId;
        $profile->gender = $this->genderId ?? $profile->gender;
        $profile->firstName = $this->firstName ?? $profile->firstName;
        $profile->lastName = $this->lastName ?? $profile->lastName;
        $profile->zipCode = $this->zipCode ?? $profile->zipCode;
        $profile->phoneNumber = $this->phoneNumber ?? $profile->phoneNumber;

        if (!$profile->save(false)) {
            throw new ErrorException('Profile was not saved');
        }

        return $profile;
    }
}
