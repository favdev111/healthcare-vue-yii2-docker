<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class NouisliderAsset extends AssetBundle
{
    public $sourcePath = '@npm/nouislider';
    public $css = [
        'distribute/nouislider.min.css',
    ];
    public $js = [
        'distribute/nouislider.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
