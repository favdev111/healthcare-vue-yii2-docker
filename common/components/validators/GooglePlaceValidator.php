<?php

namespace common\components\validators;

use yii\helpers\VarDumper;
use yii\validators\Validator;
use Yii;

class GooglePlaceValidator extends Validator
{
    public $zipCodeAttribute;
    public $latitudeAttribute;
    public $longitudeAttribute;
    public $addressAttribute;
    public $placeIdAttribute;
    public $countryCodeAttribute;
    public $stateCodeAttribute;

    public function validateAttribute($model, $attribute): void
    {
        if (empty($model->$attribute)) {
            return;
        }

        $service = Yii::$app->geocoding;
        $data = $service->getZipCodeByPlaceId($model->$attribute);
        if (false === $data) {
            $model->addError($attribute, 'Address is invalid. Please enter a valid location.');
            return;
        }

        $this->setAttribute($model, 'zipCodeAttribute', $data->zipCode);
        $this->setAttribute($model, 'latitudeAttribute', $data->latitude);
        $this->setAttribute($model, 'longitudeAttribute', $data->longitude);
        $this->setAttribute($model, 'addressAttribute', $data->address);
        $this->setAttribute($model, 'placeIdAttribute', $data->googlePlaceId);
        $this->setAttribute($model, 'countryCodeAttribute', $data->country);
        $this->setAttribute($model, 'stateCodeAttribute', $data->state);
    }

    /**
     * @param $model
     * @param string $attribute
     * @param $value
     */
    private function setAttribute($model, string $attribute, $value): void
    {
        if ($this->{$attribute}) {
            if (is_callable($this->{$attribute})) {
                call_user_func($this->{$attribute}, $value);
            } else {
                $model->{$this->{$attribute}} = $value;
            }
        }
    }
}
