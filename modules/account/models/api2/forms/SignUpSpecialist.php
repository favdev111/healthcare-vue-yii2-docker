<?php

namespace modules\account\models\api2\forms;

use api2\components\models\forms\ApiBaseForm;
use common\components\HtmlPurifier;
use common\components\validators\NameStringValidator;
use common\components\validators\StrengthValidator;
use modules\account\models\Account;
use modules\account\models\query\AccountQuery;

class SignUpSpecialist extends ApiBaseForm
{
    public $email;
    public $newPassword;
    public $phoneNumber;
    public $firstName;
    public $lastName;
    public $dateOfBirth;

    public function rules()
    {
        return [
            'required' => [['email', 'firstName', 'lastName', 'phoneNumber', 'dateOfBirth', 'newPassword'], 'required'],
            [['email', 'firstName', 'lastName', 'phoneNumber', 'dateOfBirth', 'newPassword'], 'string', 'min' => 1],
            [['newPassword'], StrengthValidator::class, 'usernameValue' => 'password'],
            [
                ['email'],
                'unique',
                'targetClass' => Account::class,
                'filter' => function ($query) {
                    /**
                     * @var $query AccountQuery
                     */
                    $query->andNonSuspended();
                },
            ],
            [['dateOfBirth'], 'date', 'format' => 'php: Y-m-d', 'min' => '1900-01-01'],
            [['firstName', 'lastName', 'phoneNumber'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
            }
            ],
            [
                ['firstName', 'lastName'],
                NameStringValidator::class
            ],
            [['firstName', 'lastName'], 'trim'],
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
