<?php

namespace backend\components;

class Theme extends \common\components\Theme
{
    public function registerJsFile($js, $depends = ['backend\assets\AppAsset'])
    {
        parent::registerJsFile($js, $depends);
    }
}
