<?php

namespace common\components\widgets\summernote\actions;

use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\UrlManager;

/**
 * Class UploadImageAction
 * @package common\components\widgets\summernote\actions
 */
class UploadImageAction extends Action
{
    public $xsendFile = false;
    public $xsendFileAction;

    public $imagePathAlias;
    public $imageUrlAlias;

    /**
     * @var UrlManager $urlManager
     */
    private $urlManager;

    /**
     * @param UrlManager $urlManager
     */
    public function setUrlManager(UrlManager $urlManager)
    {
        $this->urlManager = $urlManager;
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->imagePathAlias) {
            throw new InvalidConfigException('imagePathAlias should be configure');
        }

        if ($this->xsendFile) {
            if (!$this->xsendFileAction) {
                throw new InvalidConfigException('xsendFileAction should be configure when xsendFile is enabled');
            }
            if (!$this->urlManager) {
                throw new InvalidConfigException('urlManager should be configure when xsendFile is enabled');
            }
        } else {
            if (!$this->imageUrlAlias) {
                throw new InvalidConfigException('imageUrlAlias should be configure when xsendFile is disabled');
            }
        }
    }

    /**
     * @return array
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $file = UploadedFile::getInstanceByName('file');
        $fileName = uniqid('summernote_') . '.' . $file->extension;

        $imagePath = Yii::getAlias($this->imagePathAlias . $fileName);
        $file->saveAs($imagePath);

        if ($this->xsendFile) {
            $imageUrl = $this->urlManager->createUrl([$this->xsendFileAction, 'file' => $fileName]);
        } else {
            $imageUrl = Yii::getAlias($this->imageUrlAlias . $fileName);
        }

        return ['url' => $imageUrl];
    }
}
