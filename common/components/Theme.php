<?php

namespace common\components;

use common\helpers\Url;
use yii\helpers\Json;
use yii\web\View;
use Yii;

class Theme extends \yii\base\Theme
{
    public function registerJsFile($js, $depends = [])
    {
        $this->registerFile(
            'js',
            '/js/' . $js,
            $depends
        );
    }

    protected function registerFile($type, $path, $depends = [])
    {
        $types = ['js', 'css'];
        if (!in_array($type, $types)) {
            return null;
        }

        $functionName = 'register' . ucfirst($type) . 'File';
        $url = $this->getBaseUrl() . $path;
        if (Yii::$app->assetManager->appendTimestamp && ($timestamp = @filemtime($this->getPath($path))) > 0) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . "v=$timestamp";
        }
        Yii::$app->view->$functionName(
            $url,
            [
                'position' => View::POS_END,
                'depends' => $depends,
            ]
        );
    }

    public function getAbsoluteUrl($url)
    {
        $url = parent::getUrl($url);
        if (strpos($url, '://') === false) {
            return rtrim(Url::getFrontendUrl(), '/') . $url;
        }

        return $url;
    }
}
