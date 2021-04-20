<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class GoogleAutocompleteAsset extends AssetBundle
{
    public $sourcePath = null;
    public $css = [
    ];
    public $js = [
    ];
    public $depends = [
    ];

    public function init()
    {
        $this->js[] = 'https://maps.googleapis.com/maps/api/js?libraries=places&callback=initAutocomplete&key=' . env('GOOGLE_MAPS_API_KEY');
        parent::init();
    }
}
