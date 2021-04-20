<?php

namespace common\components\validators;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use yii\validators\Validator;

class PhoneNumberValidator extends Validator
{
    public $format = true;
    public $country = 'US';
    public $strict = true;
    public $countryAttribute;

    public function validateAttribute($model, $attribute)
    {
        // if countryAttribute is set
        if (!isset($country) && isset($this->countryAttribute)) {
            $countryAttribute = $this->countryAttribute;
            $country = $model->$countryAttribute;
        }

        // if country is fixed
        if (!isset($country) && isset($this->country)) {
            $country = $this->country;
        }

        // if none select from our models with best effort
        if (!isset($country) && isset($model->country_code)) {
            $country = $model->country_code;
        }

        if (!isset($country) && isset($model->country)) {
            $country = $model->country;
        }


        // if none and strict
        if (!isset($country) && $this->strict) {
            $this->addError($model, $attribute, \Yii::t('app', 'For phone validation country required'));
            return false;
        }

        if (!isset($country)) {
            return true;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($model->$attribute, $country);
            if ($phoneUtil->isValidNumber($numberProto)) {
                if ($this->format === true) {
                    $model->$attribute = ltrim($phoneUtil->format($numberProto, PhoneNumberFormat::E164), '+');
                }

                return true;
            } else {
                $this->addError(
                    $model,
                    $attribute,
                    \Yii::t('app', 'Phone number does not seem to be a valid phone number')
                );

                return false;
            }
        } catch (NumberParseException $e) {
            $this->addError($model, $attribute, \Yii::t('app', 'Unexpected Phone Number Format'));
        } catch (\Exception $e) {
            $this->addError($model, $attribute, \Yii::t('app', 'Unexpected Phone Number Format or Country Code'));
        }
    }
}
