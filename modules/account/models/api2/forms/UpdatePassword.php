<?php

namespace modules\account\models\api2\forms;

use api2\components\models\forms\ApiBaseForm;
use common\components\validators\StrengthValidator;

class UpdatePassword extends ApiBaseForm
{
    public $password;
    public $passwordCurrent;
    public $passwordHash;

    public function rules()
    {
        return [
            [['passwordCurrent', 'password'], 'required'],
            [['password'], StrengthValidator::class, 'usernameValue' => 'password'],
            ['passwordCurrent', function ($attribute, $params, $validator) {
                if (!\Yii::$app->security->validatePassword($this->$attribute, $this->passwordHash)) {
                    $this->addError($attribute, 'Current password incorrect.');
                }
            }
            ],
        ];
    }
}
