<?php

namespace common\components\widgets\selectize;

use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * Class SelectizeDropDownList
 * @package common\components\widgets
 */
class SelectizeDropDownList extends InputWidget
{
    /**
     * @var array
     */
    public $items = [];

    /**
     * Clear input on focus
     * @var bool
     */
    public $clearOnFocus = true;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            echo Html::dropDownList($this->name, $this->value, $this->items, $this->options);
        }

        if ($this->clearOnFocus) {
            $this->clientOptions['onFocus'] = new JsExpression("function () { this.clear(); }");
        }

        // Trigger event then loaded
        $this->clientOptions['onInitialize'] = new JsExpression("function () { jQuery(this.\$input).trigger('initialized'); }");

        parent::run();
    }
}
