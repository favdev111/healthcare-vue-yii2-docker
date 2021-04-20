<?php

namespace modules\account\models\api2Patient\forms;

use common\components\HtmlPurifier;
use common\components\validators\NameStringValidator;
use common\components\validators\StrengthValidator;
use modules\account\models\Account;
use modules\account\models\query\AccountQuery;

class SignUp extends \yii\base\Model
{
    public $email;
    public $newPassword;
    public $firstName;
    public $lastName;
    public $socialAccessToken;
    public $socialId;
    public $socialUserId;

    public function rules()
    {
        return [
            [
                [
                    'firstName',
                    'lastName',
                    'socialAccessToken',
                    'socialId',
                    'socialUserId',
                ],
                'filter',
                'filter' => function ($value) {
                    return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
                },
            ],
            [['email', 'firstName', 'lastName', 'newPassword'], 'required'],
            [['email', 'firstName', 'lastName', 'newPassword'], 'string'],
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
            [['socialAccessToken', 'socialId', 'socialUserId'], 'string'],
        ];
    }

    public function formName()
    {
        return '';
    }
}
