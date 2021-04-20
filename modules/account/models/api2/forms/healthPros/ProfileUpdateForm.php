<?php

namespace modules\account\models\api2\forms\healthPros;

use api2\components\models\forms\ApiBaseForm;
use common\components\validators\GooglePlaceValidator;
use common\components\validators\HtmlPurifierValidator;
use common\components\validators\NameStringValidator;
use common\components\validators\PhoneNumberValidator;
use modules\account\helpers\ConstantsHelper;

class ProfileUpdateForm extends ApiBaseForm
{
    public $firstName;
    public $lastName;
    public $dateOfBirth;
    public $gender;
    public $phoneNumber;
    public $placeId;

    public $googlePlaceId;
    public $zipCode;
    public $address;
    public $latitude;
    public $longitude;
    public $model;

    public function rules()
    {
        return [
            [
                [
                    'firstName',
                    'lastName',
                    'dateOfBirth',
                    'gender',
                    'zipCode',
                    'placeId',
                ],
                'required',
            ],
            [
                [
                    'firstName',
                    'lastName',
                    'phoneNumber',
                ],
                HtmlPurifierValidator::class,
            ],
            [['firstName', 'lastName'], NameStringValidator::class],
            [['gender'], 'in', 'range' => array_keys(ConstantsHelper::genderPatient())],
            ['dateOfBirth', 'date', 'format' => 'php:Y-m-d'],
            [['phoneNumber'], PhoneNumberValidator::class],
            [
                ['placeId'],
                GooglePlaceValidator::class,
                'zipCodeAttribute' => 'zipCode',
                'placeIdAttribute' => 'googlePlaceId',
                'addressAttribute' => 'address',
                'latitudeAttribute' => 'latitude',
                'longitudeAttribute' => 'longitude',
            ],
        ];
    }

    public function formName()
    {
        return '';
    }
}
