<?php

namespace common\assets;

use yii\web\AssetBundle;

class SignaturePadAsset extends AssetBundle
{

    public $sourcePath = '@npm';
    public $js = [
        'signature_pad/dist/signature_pad.min.js',
    ];
}
