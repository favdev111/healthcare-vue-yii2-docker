<?php

namespace common\assets;

use yii\web\AssetBundle;

class JobOfferAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/common/assets/dist/js/account';

    public $js = [
        'job-offer.js',
    ];
}
