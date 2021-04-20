<?php
namespace themes\basic\backend\bundles;

use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Stisla AssetBundle
 * @since 0.1
 */
class StislaAsset extends AssetBundle
{
    public $sourcePath = '@node_modules/stisla/assets/';
    public $css = [
        'css/style.css',
        'css/components.min.css',
    ];
    public $js = [
        'js/custom.js',
        'js/stisla.js',
    ];

    public $depends = [
        FontawesomeAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        NiceScrollAsset::class,
        JqueryAsset::class
    ];
}
