<?php

namespace modules\account\widgets\assets;

use yii\web\AssetBundle;

class ImageCropAndUpload extends AssetBundle
{
    public $sourcePath = '@modules/account/widgets/assets/src/imageCropAndUpload';
    public $css = [];
    public $js = [
        'js/main.js',
    ];
    public $depends = [
        'frontend\assets\ExifAsset',
        'common\assets\CropperAsset',
    ];
}
