<?php

namespace common\components\widgets\summernote;

use yii\base\InvalidConfigException;
use yii\web\JsExpression;
use marqu3s\summernote\Summernote as BaseSummernote;

/**
 * Class Summernote
 * @package common\components\widgets
 */
class Summernote extends BaseSummernote
{
    public $uploadImageUrl;
    public $uploadFailedMessage = 'Failed to upload file';

    public $preset;
    const PRESET_BASE = 'base';
    const PRESETS = [
        self::PRESET_BASE => [
            ["style", ["style"]],
            ["font", ["bold", "underline", "clear"]],
            ["fontname", ["fontname"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["table", ["table"]],
            ["insert", ["link", "picture", "video"]],
            ["view", [ "help"]]
        ],
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!isset($this->options['id'])) {
            throw new InvalidConfigException('id should be configured');
        }

        if (!$this->uploadImageUrl) {
            throw new InvalidConfigException('uploadImageUrl should be configured');
        }

        if (isset(self::PRESETS[$this->preset])) {
            $this->clientOptions['toolbar'] = self::PRESETS[$this->preset];
        }

        $this->clientOptions['callbacks'] = [
            'onImageUpload' => new JsExpression('function (files, editor, welEditable) {
                sendFile(files[0], editor, welEditable);
        }'),
        ];

        parent::run();


        $this->getView()->registerJs('
        $(window).on(\'shown.bs.modal\', function() {
                $(\'#sn-checkbox-open-in-new-window\').hide();
                $(\'#sn-checkbox-open-in-new-window\')
                    .closest( ".checkbox" ).hide()
        });
        
        function sendFile(file, editor, welEditable) {
            data = new FormData();
            data.append("file", file);
            $.ajax({
                data: data,
                type: "POST",
                url: "' . $this->uploadImageUrl . '",
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    $(\'#' . $this->options['id'] . '\').summernote("insertImage", data.url);
                },
                error: function() {
                    toastr.error(\'' . $this->uploadFailedMessage . '\')
                }
            });
        }');
    }
}
