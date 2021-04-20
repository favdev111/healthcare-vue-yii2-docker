<?php

namespace modules\account\models\api2\forms;

use api2\components\models\forms\ApiBaseForm;

class PasswordRecovery extends ApiBaseForm
{
    public $email;

    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
        ];
    }
}
