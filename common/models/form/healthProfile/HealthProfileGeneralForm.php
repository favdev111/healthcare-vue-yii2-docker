<?php

namespace common\models\form\healthProfile;

use common\components\validators\BirthdayValidator;
use common\components\validators\EmailValidator;
use common\components\validators\HtmlPurifierValidator;
use common\components\validators\NameStringValidator;
use common\components\validators\PhoneNumberValidator;
use common\components\validators\GooglePlaceValidator;
use modules\account\helpers\ConstantsHelper;
use yii\base\Model;

class HealthProfileGeneralForm extends Model
{
    public $relationshipId;
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $email;
    public $birthday;
    public $gender;
    public $googlePlaceId;
    public $maritalStatusId;
    public $educationLevelId;
    public $childrenCount;
    public $occupation;
    public $employer;
    public $height;
    public $weight;

    public $address;
    public $zipcode;
    public $country;
    public $state;
    public $latitude;
    public $longitude;

    public $model;

    public function rules()
    {
        $thenMainProfile = function () {
            return $this->model && $this->model->isMain;
        };
        $thenNotMainProfile = function () {
            return !$this->model || !$this->model->isMain;
        };

        return [
            [
                [
                    'firstName',
                    'lastName',
                    'birthday',
                    'gender',
                    'height',
                    'weight',
                    'googlePlaceId',
                ],
                'required',
            ],
            [
                [
                    'phoneNumber',
                    'email',
                ],
                'required',
                'when' => $thenMainProfile,
            ],
            [
                ['relationshipId'],
                'required',
                'when' => $thenNotMainProfile,
            ],
            [
                ['relationshipId'],
                'in',
                'range' => array_keys(ConstantsHelper::relationship()),
                'when' => $thenNotMainProfile,
            ],
            [
                [
                    'firstName',
                    'lastName',
                    'phoneNumber',
                    'email',
                ],
                HtmlPurifierValidator::class,
            ],
            [
                [
                    'occupation',
                    'employer',
                ],
                HtmlPurifierValidator::class,
                'when' => $thenMainProfile,
            ],
            [['email'], EmailValidator::class],
            [['firstName', 'lastName'], NameStringValidator::class],
            [['gender'], 'in', 'range' => array_keys(ConstantsHelper::genderPatient())],
            [
                ['maritalStatusId'],
                'in',
                'range' => array_keys(ConstantsHelper::maritalStatus()),
                'when' => $thenMainProfile,
            ],
            [
                ['educationLevelId'],
                'in',
                'range' => array_keys(ConstantsHelper::educationLevel()),
                'when' => $thenMainProfile,
            ],
            [
                ['birthday'],
                BirthdayValidator::class,
                'min' => 18,
                'tooSmall' => 'Adults 18 years and older must create their own accounts.',
                'when' => $thenNotMainProfile,
            ],
            [
                ['birthday'],
                BirthdayValidator::class,
                'max' => 18,
                'tooBig' => 'Minors under 18 years of age must be added as dependents under parents or guardians accounts.',
                'when' => $thenMainProfile,
            ],
            [['phoneNumber'], PhoneNumberValidator::class],
            [['childrenCount'], 'integer', 'min' => 0, 'max' => 20, 'when' => $thenMainProfile],
            [
                ['googlePlaceId'],
                GooglePlaceValidator::class,
                'zipCodeAttribute' => 'zipcode',
                'addressAttribute' => 'address',
                'stateCodeAttribute' => 'state',
                'countryCodeAttribute' => 'country',
                'latitudeAttribute' => 'latitude',
                'longitudeAttribute' => 'longitude',
            ],
            [['occupation', 'employer'], 'string', 'max' => 65000, 'when' => $thenMainProfile],
            [
                ['height'], // data in feet
                'number',
                'min' => 0,
                'max' => 8 * 12, // 1ft === 12in
                'tooSmall' => 'Height must be no less than {min} inches.',
                'tooBig' => 'Height must be no greater than {max} feet.',
            ],
            [['weight'], 'number', 'min' => 0, 'max' => 1000],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'maritalStatusId' => 'Marital status',
            'placeId' => 'Address',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = $scenarios[parent::SCENARIO_DEFAULT];

        return $scenarios;
    }
}
