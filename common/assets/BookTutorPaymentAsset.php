<?php

namespace common\assets;

use yii\web\AssetBundle;

class BookTutorPaymentAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/common/assets/dist';

    public $js = [
        'js/payment.js',
    ];

    public $depends = [
        'common\assets\CommonAsset',
    ];
}
