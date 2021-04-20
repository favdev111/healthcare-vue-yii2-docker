<?php

namespace backend\components\widgets\content;

use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Class AjaxContentLoader
 * @package backend\components\widgets\content
 */
class AjaxContentLoader extends Widget
{
    /**
     * @var string Url to page
     */
    public $url;
    /**
     * @var string Icon spinner
     */
    public $iconLoader = '<div id="ajax-content-loader"><div class="spinner-border text-primary"><span class="sr-only"></span></div></div>';
    /**
     * @var array
     */
    public array $contentOptions = [];

    public function init()
    {
        parent::init();

        if ($this->url === null) {
            throw new InvalidConfigException('The "url" property must be set.');
        }
    }

    /**
     * @return string|void
     */
    public function run()
    {
        parent::run();

        $js = <<<JS
        $("#$this->id").html('$this->iconLoader');
        $.ajax({
             url: "$this->url",
             type: 'get',
             error: function(XMLHttpRequest, textStatus, errorThrown){
                 $("#$this->id").html(errorThrown);
             },
             success: function(data){
                 $("#$this->id").html(data);
             }
        });
JS;

        $this->getView()->registerJs($js);
        $contentOptions = array_merge(['id' => $this->id, 'style' => ['position' => 'relative']], $this->contentOptions);
        return Html::tag('div', '', $contentOptions);
    }
}
