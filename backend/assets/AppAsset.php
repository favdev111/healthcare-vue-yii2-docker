<?php

namespace backend\assets;

use backend\components\widgets\googlePlace\GooglePlaceAsset;
use kartik\select2\Select2Asset;
use themes\basic\backend\bundles\StislaAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/backend/assets/dist';
    public $css = [
        'css/style.min.css',
        'css/custom.css',
    ];
    public $js = [
        'js/reviews.js',
        'js/main.js',
        'js/stisla/scripts.js'
    ];
    public $depends = [
        Select2Asset::class,
        YiiAsset::class,
        StislaAsset::class,
        GooglePlaceAsset::class,
    ];
}
