<?php

namespace modules\account\models\api2Patient\forms;

use common\components\HtmlPurifier;
use common\components\validators\NameStringValidator;
use modules\account\models\Account;
use modules\account\models\query\AccountQuery;

class Update extends \yii\base\Model
{
    public $email;
    public $firstName;
    public $lastName;
    public $phoneNumber;

    public $accountId;

    public function rules()
    {
        return [
            [
                [
                    'firstName',
                    'lastName',
                    'phoneNumber',
                    'email',
                ],
                'filter',
                'filter' => function ($value) {
                    return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
                },
            ],
            [['email', 'firstName', 'lastName', 'phoneNumber'], 'required'],
            [['email', 'firstName', 'lastName', 'phoneNumber'], 'string'],
            [
                ['email'],
                'unique',
                'targetClass' => Account::class,
                'filter' => function ($query) {
                    /**
                     * @var $query AccountQuery
                     */
                    $query->andNonSuspended();
                    $query->andWhere(['<>', 'id', $this->accountId]);
                },
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
            [
                ['phoneNumber'],
                \udokmeci\yii2PhoneValidator\PhoneValidator::class,
                'country' => 'US',
                'format' => false,
            ],
        ];
    }

    public function formName()
    {
        return '';
    }
}
