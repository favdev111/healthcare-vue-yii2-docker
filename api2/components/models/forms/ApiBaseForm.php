<?php

namespace api2\components\models\forms;

use yii\base\Model;

/**
 * Class ApiBaseForm
 * @package api2\components\models\forms
 */
abstract class ApiBaseForm extends Model
{
    public function formName()
    {
        return '';
    }
}
