<?php

namespace modules\account\models\api;

class ZipCode extends \common\models\Zipcode
{
    public function rules()
    {
        return [
          [['code'], 'integer'],
          [['code'], 'exist', 'skipOnError' => true, 'targetAttribute' => 'code' ]
        ];
    }

    public function extraFields()
    {
        return [
            'city'
        ];
    }
}
