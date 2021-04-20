<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/frontend/assets/dist';
    public $css = [
        'css/style.min.css',
    ];
    public $js = [
        'js/main.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        '\rmrevin\yii\fontawesome\AssetBundle',
        '\common\assets\ToastrAsset',
        'common\assets\CommonAsset',
        'yii\widgets\MaskedInputAsset',
    ];
}
