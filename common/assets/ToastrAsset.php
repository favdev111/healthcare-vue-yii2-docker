<?php

namespace common\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class ToastrAsset extends AssetBundle
{
    public $sourcePath = '@bower/toastr';
    public $css = [
        'toastr.min.css',
    ];
    public $js = [
        'toastr.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
