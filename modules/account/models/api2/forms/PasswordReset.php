<?php

namespace modules\account\models\api2\forms;

use api2\components\models\forms\ApiBaseForm;
use common\components\validators\StrengthValidator;

class PasswordReset extends ApiBaseForm
{
    public $password;

    public function rules()
    {
        return [
            [['password'], 'required'],
            [['password'], StrengthValidator::class, 'usernameValue' => 'password'],
        ];
    }
}
