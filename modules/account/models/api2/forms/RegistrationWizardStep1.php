<?php

namespace modules\account\models\api2\forms;

use common\components\HtmlPurifier;
use common\components\validators\GooglePlaceValidator;
use common\components\validators\NameStringValidator;
use modules\account\models\api2\Profile;
use yii\base\Model;

class RegistrationWizardStep1 extends Model
{
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $gender;
    public $dateOfBirth;
    public $placeId;

    public $googlePlaceId;
    public $zipCode;
    public $address;
    public $latitude;
    public $longitude;

    public function rules()
    {
        return [
            [['firstName', 'lastName', 'phoneNumber', 'dateOfBirth', 'placeId', 'gender'], 'required'],
            ['gender', 'in', 'range' => array_keys(Profile::getGenderArray())],
            [['dateOfBirth'], 'date', 'format' => 'php: Y-m-d', 'min' => '1900-01-01'],
            [['firstName', 'lastName', 'phoneNumber', 'placeId'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
            }
            ],
            [
                'placeId',
                GooglePlaceValidator::class,
                'zipCodeAttribute' => 'zipCode',
                'placeIdAttribute' => 'googlePlaceId',
                'addressAttribute' => 'address',
                'latitudeAttribute' => 'latitude',
                'longitudeAttribute' => 'longitude',
            ],
            [
                ['firstName', 'lastName'],
                NameStringValidator::class
            ],
            ['firstName', 'filter', 'filter' => function ($value) {
                return ucwords(strtolower($value));
            }
            ],
            [['firstName', 'lastName'], 'string', 'max' => 255],
            ['phoneNumber', 'string', 'max' => 10],
            ['phoneNumber', 'udokmeci\yii2PhoneValidator\PhoneValidator', 'country' => 'US', 'format' => false],
            [
                'dateOfBirth',
                'date',
                'format' => 'php:Y-m-d',
                'timestampAttribute' => 'dateOfBirth',
                'timestampAttributeFormat' => 'php:Y-m-d',
                'max' => date('Y-m-d', strtotime('-18 year')),
                'tooBig' => 'To Submit application you should be 18+ years old'
            ],
        ];
    }
}
