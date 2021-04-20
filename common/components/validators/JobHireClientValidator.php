<?php

namespace common\components\validators;

use modules\account\models\JobHire;
use yii\validators\Validator;

class JobHireClientValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $jobHireId = $model->$attribute;
        if (!empty($accountClientId)) {
            $isJobHireExists = JobHire::find()
                ->joinWith('job.account')
                ->andWhere([JobHire::tableName() . '.id' => $jobHireId])
                ->exists();
            if (!$isJobHireExists) {
                $this->addError($model, $attribute, 'Invalid jobHireId');
            }
        }
    }
}
