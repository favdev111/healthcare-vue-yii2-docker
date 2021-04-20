<?php

namespace backend\components\widgets\inputs;

use kartik\select2\Select2;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * Class Select2Ajax
 * @package backend\components\widgets\fields
 */
class Select2Ajax extends Select2
{
    /**
     * @var string
     */
    public $theme = Select2::THEME_DEFAULT;
    /**
     * @var string|array
     */
    public $route;
    /**
     * @var string
     */
    public string $waitingPlaceholder = 'Waiting for results...';

    public function init()
    {
        if (!$this->route) {
            throw new InvalidConfigException('property Select2Ajax::route must be set');
        }

        $defaultOptions = [
            'multiple' => true,
            'placeholder' => 'Search...',
        ];
        $this->options = array_merge($defaultOptions, $this->options);

        $defaultPluginOptions = [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'language' => [
                'errorLoading' => new JsExpression("function () { return '{$this->waitingPlaceholder}'; }"),
            ],
            'ajax' => [
                'url' => Url::toRoute($this->route),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(item) { return item.text; }'),
            'templateSelection' => new JsExpression('function (item) { return item.text; }')
        ];
        $this->pluginOptions = array_merge($defaultPluginOptions, $this->pluginOptions);

        parent::init();
    }
}
