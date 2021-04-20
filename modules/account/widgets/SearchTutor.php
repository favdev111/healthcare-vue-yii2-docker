<?php

namespace modules\account\widgets;

use yii\base\Widget;

class SearchTutor extends Widget
{
    public $template;

    public function run()
    {
        return $this->render($this->template);
    }
}
