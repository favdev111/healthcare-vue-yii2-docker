<?php

namespace common\components\validators;

use common\components\Twilio;
use modules\account\models\AccountPhone;
use modules\account\models\PhoneValidation;
use yii\base\Model;
use yii\validators\Validator;

class TwilioPhoneValidator extends Validator
{
    protected function isValidationEnabled(): bool
    {
        $env = env('DISABLE_TWILIO_PHONE_VALIDATION');
        return !((bool)$env);
    }

    protected function processError(Model $model, $attribute, $response)
    {
        $this->addError($model, $attribute, 'Phone number ' . $model->$attribute . ' in invalid. Please contact us for more details.');
        $phoneValidation = new PhoneValidation();
        $phoneValidation->phoneNumber = $model->$attribute;
        $phoneValidation->response = $response;
        $phoneValidation->save();
    }
    public function validateAttribute($model, $attribute)
    {
        if (!$this->isValidationEnabled()) {
            $model->validationResponse = 'Success validation (test mode).';
            $model->validationPhoneType = PhoneValidation::TYPE_MOBILE;
            $model->validationPhoneStatus = PhoneValidation::STATUS_VALID;
            return true;
        }

        /**
         * @var AccountPhone $model
         * @var Twilio $twilio
         */
        $twilio = \Yii::$app->twilio;
        try {
            try {
                $response = $twilio->getPhoneInfo($model->$attribute);
            } catch (\Throwable $exception) {
                $this->processError($model, $attribute, $exception->getMessage());
                return false;
            }
            $model->validationResponse = $response;
            $label = $response->carrier['type'];
            if (empty($label)) {
                $this->processError($model, $attribute, $response);
                return false;
            }
            $type = PhoneValidation::getTypeByLabel($label);
            $model->validationPhoneType = $type;
            $isValid = PhoneValidation::isTypeValid($type);
            if (!$isValid) {
                $this->processError($model, $attribute, $response);
                return false;
            }
            $model->validationPhoneStatus = (int)$isValid;
        } catch (\Throwable $exception) {
            $this->addError($model, $attribute, $exception->getMessage());
        }
        return true;
    }
}
