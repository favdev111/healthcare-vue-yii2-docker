<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * SwiperAsset asset bundle.
 */
class SwiperAsset extends AssetBundle
{
    public $sourcePath = '@themes/basic/frontend/assets/dist';
    public $js = [
        'libs/swiper.min.js',
    ];
}
