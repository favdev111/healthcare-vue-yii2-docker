<?php

namespace common\components\widgets\selectize;

use yii\helpers\Html;

/**
 * SelectizeTextInput
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class SelectizeTextInput extends InputWidget
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }

        parent::run();
    }
}
