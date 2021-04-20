<?php

namespace common\assets;

use yii\web\AssetBundle;

class CropperAsset extends AssetBundle
{
    public $sourcePath = '@npm';
    public $css = [
        'cropper/dist/cropper.min.css',
    ];
    public $js = [
        'cropper/dist/cropper.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
