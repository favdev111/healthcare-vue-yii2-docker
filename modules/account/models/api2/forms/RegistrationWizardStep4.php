<?php

namespace modules\account\models\api2\forms;

use common\components\HtmlPurifier;
use modules\account\models\api2\EducationCollege;
use modules\account\models\EducationDegree;
use yii\base\Model;

class RegistrationWizardStep4 extends Model
{
    public $educations = [];
    public $certifications = [];
    public function rules()
    {
        return [
            [
                ['educations'],
                'required'
            ],
            [
                'educations',
                'educationsValidator'
            ],
            [
                'certifications',
                'certificationsValidator',
            ],
        ];
    }

    public function educationsValidator($attribute, $params, $validator)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute . ' must be array');
            return false;
        }
        foreach ($this->$attribute as $data) {
            if (!is_array($data)) {
                $this->addError($attribute . ' - must be array of objects.');
                return false;
            }
            $requiredFields = ['collegeId', 'degreeId', 'graduated'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $this->addError('educations', $field . " is required.");
                    continue;
                }
                $data[$field] = (int)($data[$field]);
                if (!is_numeric($data[$field]) || $data[$field] < 0) {
                    $this->addError('educations', "Invalid $field value");
                }
            }

            if (
                !empty($data['collegeId'])
                && !EducationCollege::find()->andWhere(['id' => $data['collegeId']])->exists()
            ) {
                $this->addError('educations', 'Invalid college provided');
            }

            if (
                !empty($data['degreeId'])
                && !EducationDegree::find()->andWhere(['id' => $data['degreeId']])->exists()
            ) {
                $this->addError('educations', 'Invalid degree provided');
            }

            if (!empty($data['graduated']) && $data['graduated'] < 1900) {
                $this->addError('educations', 'Graduated must be grater than 1900');
            }
        }
    }

    public function certificationsValidator($attribute, $params, $validator)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute . ' must be array');
            return false;
        }
        foreach ($this->$attribute as $data) {
            if (!is_array($data)) {
                $this->addError($attribute . ' - must be array of objects.');
                return false;
            }
            $requiredFields = ['certificateName', 'yearReceived'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $this->addError('certifications', $field . " is required.");
                    continue;
                }
                $data[$field] = (int)($data[$field]);
                if (!is_numeric($data[$field]) || $data[$field] < 0) {
                    $this->addError('certifications', "Invalid $field value");
                }
            }
            if (!empty($data['yearReceived']) && $data['yearReceived'] < 1900) {
                $this->addError('certifications', 'Year must be grater than 1900');
            }
        }
    }
}
