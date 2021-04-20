<?php

namespace backend\components\widgets\googlePlace;

use yii\web\AssetBundle;
use yii\web\AssetManager;

/**
 * Class GooglePlaceAsset
 * @package backend\components\widgets\googlePlace
 */
class GooglePlaceAsset extends AssetBundle
{
    /**
     * @var array
     */
    public $googleParams = [];

    public $js = [
        'google' => '//maps.googleapis.com/maps/api/js',
    ];

    public function init()
    {
        parent::init();
        if ($this->googleParams) {
            $googleParams = http_build_query($this->googleParams);
            $this->js['google'] .= "?{$googleParams}";
        }
    }
}
