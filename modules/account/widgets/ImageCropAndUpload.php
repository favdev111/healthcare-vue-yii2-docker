<?php

namespace modules\account\widgets;

use modules\account\widgets\assets\ImageCropAndUpload as Assets;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Crop and upload image
 */
class ImageCropAndUpload extends Widget
{
    public $inputFileImageSelector = '#inputFileImage';

    /** @var array HTML widget options */
    public $options = [];

    /** @var array Default HTML-options for image tag */
    public $defaultImageOptions = [
        'class' => 'cropper-image img-responsive',
        'alt' => 'crop-image',
    ];

    /** @var array HTML-options for image tag */
    public $imageOptions = [
        'style' => 'max-height: 600px;',
    ];

    /** @var array Default cropper options https://github.com/fengyuanchen/cropper/blob/master/README.md#options */
    public $defaultPluginOptions = [
        'aspectRatio' => 1,
        'strict' => true,
        'autoCropArea' => 1,
        'checkCrossOrigin' => false,
        'checkOrientation' => true,
        'minCropBoxWidth' => 128,
        'minCropBoxHeight' => 128,
        'viewMode' => 1,
    ];

    /** @var array Additional cropper options https://github.com/fengyuanchen/cropper/blob/master/README.md#options */
    public $pluginOptions = [];

    public $callbackJs;

    /** @var array Ajax options for send crop-reques */
    public $ajaxOptions = [];

    public function run()
    {
        $view = $this->getView();
        Assets::register($view);

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        } else {
            $this->setId($this->options['id']);
        }

        $this->pluginOptions = ArrayHelper::merge($this->defaultPluginOptions, $this->pluginOptions);
        $this->imageOptions = ArrayHelper::merge($this->defaultImageOptions, $this->imageOptions);

        return $this->render(
            'imageCropAndUpload',
            [
                'widget' => $this,
                'inputFileImageSelector' => $this->inputFileImageSelector,
                'callback' => $this->callbackJs ?? 'undefined',
                'ajaxOptions' => Json::encode($this->ajaxOptions),
                'pluginOptions' => Json::encode($this->pluginOptions),
            ]
        );
    }
}
