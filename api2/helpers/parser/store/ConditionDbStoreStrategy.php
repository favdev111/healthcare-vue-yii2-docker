<?php

namespace api2\helpers\parser\store;

use common\models\health\MedicalCondition;

class ConditionDbStoreStrategy implements \api2\helpers\parser\store\ParserStoreStrategy
{
    public function batch($data)
    {
        foreach ($data as $name) {
            $this->store($name);
        }
    }

    public function store($name)
    {
        try {
            if (!MedicalCondition::find()->andWhere(['name' => $name])->exists()) {
                $model = new MedicalCondition();
                $model->name = $name;
                $model->save();
            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }
}
