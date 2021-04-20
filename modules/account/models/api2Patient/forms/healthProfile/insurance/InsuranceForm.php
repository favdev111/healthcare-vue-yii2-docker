<?php

namespace modules\account\models\api2Patient\forms\healthProfile\insurance;

use api2\components\models\forms\ApiBaseForm;
use common\components\validators\GooglePlaceValidator;
use common\components\validators\HtmlPurifierValidator;
use common\components\validators\NameStringValidator;
use common\models\healthProfile\HealthProfile;
use modules\account\models\api\ZipCode;
use modules\account\models\api2Patient\entities\healthProfile\insurance\HealthProfileInsurance;
use modules\account\models\ar\InsuranceCompany;
use Yii;
use yii\base\ErrorException;
use yii\validators\UniqueValidator;

/**
 * Class InsuranceForm
 * @package modules\account\models\api2Patient\forms\healthProfile\insurance
 */
abstract class InsuranceForm extends ApiBaseForm
{
    /**
     * @var string
     */
    public $insuranceCompanyId;
    /**
     * @var string
     */
    public $groupNumber;
    /**
     * @var string
     */
    public $policyNumber;
    /**
     * @var string
     */
    public $googlePlaceId;
    /**
     * @var string
     */
    public $dateOfBirth;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var string
     */
    public $socialSecurityNumber;
    /**
     * @var string
     */
    public $isPrimary;

    /**
     * @var ZipCode
     */
    protected $locationZipCode;
    /**
     * @var string
     */
    protected $address;
    /**
     * @var HealthProfile
     */
    protected $healthProfile;

    /**
     * CreateInsuranceForm constructor.
     * @param HealthProfile $healthProfile
     * @param array $config
     */
    public function __construct(HealthProfile $healthProfile, array $config = [])
    {
        $this->healthProfile = $healthProfile;
        parent::__construct($config);
    }

    /**
     * @return array|array[]
     */
    public function rules()
    {
        return [
            [
                [
                    'policyNumber',
                    'googlePlaceId',
                    'firstName',
                    'lastName',
                    'socialSecurityNumber',
                ],
                HtmlPurifierValidator::class, 'skipOnEmpty' => true
            ],
            [
                [
                    'policyNumber',
                    'googlePlaceId',
                    'firstName',
                    'lastName',
                    'socialSecurityNumber',
                ],
                'trim',
                'skipOnEmpty' => true
            ],
            ['insuranceCompanyId', 'integer'],
            ['isPrimary', 'boolean'],
            ['dateOfBirth', 'date', 'max' => date('Y-m-d'), 'format' => 'php:Y-m-d'],
            [['groupNumber', 'policyNumber', 'socialSecurityNumber'], 'string', 'min' => 2, 'max' => 50],
            [['googlePlaceId', 'firstName', 'lastName'], 'string', 'min' => 2, 'max' => 255],
            [['firstName', 'lastName'], NameStringValidator::class, 'skipOnEmpty' => true],
            [
                ['insuranceCompanyId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => InsuranceCompany::class,
                'targetAttribute' => ['insuranceCompanyId' => 'id']
            ],
            [
                'googlePlaceId',
                GooglePlaceValidator::class,
                'zipCodeAttribute' => function ($zipcode) {
                    $this->initZipCode($zipcode);
                },
                'addressAttribute' => function ($address) {
                    $this->address = $address;
                },
            ],
            [
                'isPrimary',
                function () {
                    $name = $this->isPrimary ? 'Primary' : 'Secondary';
                    $message = "{$name} insurance has already been taken.";

                    $validator = Yii::createObject([
                        'class' => UniqueValidator::class,
                        'targetClass' => HealthProfileInsurance::class,
                        'filter' => ['healthProfileId' => $this->healthProfile->id],
                        'targetAttribute' => 'isPrimary',
                        'message' => $message,
                    ]);

                    $validator->validateAttribute($this, 'isPrimary');
                }
            ],
        ];
    }

    /**
     * @param $zipcode
     * @return false
     */
    private function initZipCode($zipcode)
    {
        $locationZipCode = ZipCode::findOne(['code' => $zipcode]);
        if (!$locationZipCode) {
            $message = Yii::t(
                'yii',
                '{attribute} is not a valid email address.',
                ['attribute' => 'googlePlaceId']
            );
            $this->addError('googlePlaceId', $message);
            return false;
        }
        $this->locationZipCode = $locationZipCode;
    }

    /**
     * @param HealthProfileInsurance $healthProfileInsurance
     * @return HealthProfileInsurance
     * @throws ErrorException
     */
    public function buildHealthProfileInsurance(HealthProfileInsurance $model): HealthProfileInsurance
    {
        $model->healthProfileId = $this->healthProfile->id ?? $model->healthProfileId;
        $model->policyNumber = $this->policyNumber ?? $model->policyNumber;
        $model->insuranceCompanyId = $this->insuranceCompanyId ?? $model->insuranceCompanyId;
        $model->groupNumber = $this->groupNumber ?? $model->groupNumber;
        $model->locationZipCodeId = $this->locationZipCode->id ?? $model->locationZipCodeId;
        $model->address = $this->address ?? $model->address;
        $model->googlePlaceId = $this->googlePlaceId ?? $model->googlePlaceId;
        $model->dateOfBirth = $this->dateOfBirth ?? $model->dateOfBirth;
        $model->firstName = $this->firstName ?? $model->firstName;
        $model->lastName = $this->lastName ?? $model->lastName;
        $model->socialSecurityNumber = $this->socialSecurityNumber ?? $model->socialSecurityNumber;
        $model->isPrimary = $this->isPrimary ?? $model->isPrimary;

        if (!$model->save()) {
            throw new ErrorException('HealthProfileInsurance model was not saved');
        }

        return $model;
    }
}
