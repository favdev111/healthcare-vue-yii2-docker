<?php

namespace backend\models;

class InsuranceCompany extends \modules\account\models\ar\InsuranceCompany
{
    public function rules()
    {
        return [
            ['name', 'string'],
            ['name', 'required']
        ];
    }
}
