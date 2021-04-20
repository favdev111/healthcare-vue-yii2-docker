<?php

namespace common\components\widgets\summernote\assets;

use yii\web\AssetBundle;

class SummnernoteAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/frontend/assets/dist';

    public $js = [
        'js/summernote.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
