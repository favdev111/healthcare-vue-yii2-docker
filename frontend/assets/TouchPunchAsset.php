<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class TouchPunchAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/frontend/assets/dist';
    public $css = [
    ];
    public $js = [
        'libs/jquery.ui.touch-punch.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
