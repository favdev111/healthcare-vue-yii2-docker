<?php

namespace backend\components\widgets\googlePlace;

use yii\bootstrap4\Html;
use yii\bootstrap4\InputWidget;

/**
 * Class GooglePlacesAutoComplete
 * @package backend\components\widgets\googlePlace
 */
class GooglePlacesAutoComplete extends InputWidget
{
    /**
     * @var array
     */
    public $autocompleteOptions = [];
    /**
     * @var string Event
     */
    public $onPlaceChanged = '() => {}';

    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->registerClientScript();

        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript()
    {
        $elementId = $this->options['id'];
        $view = $this->getView();
        GooglePlaceAsset::register($view);
        $scriptOptions = json_encode($this->autocompleteOptions);
        $js = <<<JS
    var input = document.getElementById('{$elementId}');
    var options = {$scriptOptions};
    var autocomplete = new google.maps.places.Autocomplete(input, options);
    autocomplete.addListener("place_changed", {$this->onPlaceChanged});

JS;
        $view->registerJs($js);
    }
}
