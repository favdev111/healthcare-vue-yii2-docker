<?php

namespace common\components\widgets\selectize;

use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use dosamigos\selectize\SelectizeAsset;

/**
 *  @inheritdoc
 *
 */
class InputWidget extends \dosamigos\selectize\InputWidget
{
    /**
     * is init ajax load enabled
     * @var bool
     */
    public $initLoad = false;

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript()
    {
        $id = $this->options['id'];

        if ($this->loadUrl !== null) {
            $url = Url::to($this->loadUrl);

            $preventPreload = ($this->initLoad) ? '' : 'if (!query.length) return callback();';
            $this->clientOptions['load'] = new JsExpression(<<<JS
function (query, callback) {
   $preventPreload
   $.getJSON('$url', { query: query }, function (data) {
     callback(data.results ? data.results.slice(0) : data);
   }).fail(function () { callback(); }); }
JS
            );
        }

        $options = Json::encode($this->clientOptions);
        $view = $this->getView();
        SelectizeAsset::register($view);


        $view->registerJs("jQuery('#$id').selectize($options); ");
    }
}
