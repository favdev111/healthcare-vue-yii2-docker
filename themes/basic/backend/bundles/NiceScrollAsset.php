<?php

namespace themes\basic\backend\bundles;

use yii\web\AssetBundle;

class NiceScrollAsset extends AssetBundle
{
    public $sourcePath = '@node_modules/jquery.nicescroll/';
    public $css = [];
    public $js = [
        'dist/jquery.nicescroll.js',
    ];
}
