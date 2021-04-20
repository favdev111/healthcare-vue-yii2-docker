<?php

namespace common\assets;

use yii\web\AssetBundle;

class CommonAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/common/assets/dist';

    public $js = [
        'js/helpers.js',
        'js/main.js'
    ];

    public $depends = [
        'common\assets\ToastrAsset',
    ];
}
