<?php

namespace common\components\behaviors;

use yii\base\Behavior;

class ChildModelErrorsBehavior extends Behavior
{
    public function collectErrors($childModel)
    {
        $errors = $childModel->getErrors();
        if ($errors) {
            $this->owner->addErrors($errors);
        }
    }
}
