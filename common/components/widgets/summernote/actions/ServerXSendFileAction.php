<?php

namespace common\components\widgets\summernote\actions;

use yii\base\Action;
use Yii;

/**
 * Class ServerXSendFileAction
 * @package common\components\widgets\summernote\actions
 */
class ServerXSendFileAction extends Action
{
    public $imageUrl;

    /**
     * @param $file
     * @return $this
     */
    public function run($file)
    {
        $fullFileUrl = $this->imageUrl . '/' . $file;
        return Yii::$app->response->xSendFile(
            $fullFileUrl,
            null,
            [
                'xHeader' => 'X-Accel-Redirect',
            ]
        );
    }
}
