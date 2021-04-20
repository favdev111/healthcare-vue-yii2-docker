<?php

namespace modules\account\models\api2\forms\healthPros;

use api2\components\models\forms\ApiBaseForm;

class RatePolicyForm extends ApiBaseForm
{
    public $ratePolicy;
    public $rate15;
    public $rate30;
    public $rate45;
    public $rate60;


    public function rules()
    {
        return [
            //[['ratePolicy'], 'required', 'requiredValue' => true, 'message' => 'Please agree with Rate Policy'],
            [
                ['rate15', 'rate30', 'rate45', 'rate60'],
                'ratesValidator'
            ],
            [
                ['rate15', 'rate30', 'rate45', 'rate60'],
                'double',
                'min' => 20,
                'max' => 300,
            ],
        ];
    }

    public function ratesValidator()
    {
        if (
            empty($this->rate15)
            && empty($this->rate30)
            && empty($this->rate45)
            && empty($this->rate60)
        ) {
            $this->addError('rate15', 'At least one rate must be provided.');
        }
    }
}
