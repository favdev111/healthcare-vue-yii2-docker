<?php

namespace common\helpers;

use common\assets\ToastrAsset;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

class FormError
{
    public static function show($models, $options = [])
    {
        $encode = ArrayHelper::remove($options, 'encode', true);
        if (!is_array($models)) {
            $models = [$models];
        }
        $lines = [];
        foreach ($models as $model) {
            if (!($model instanceof Model)) {
                continue;
            }

            /* @var $model Model */
            foreach ($model->getFirstErrors() as $error) {
                $lines[] = $encode ? Html::encode($error) : $error;
            }
        }

        if (!empty($lines)) {
            /** @var $view View */
            $view = \Yii::$app->view;
            ToastrAsset::register($view);

            $errorsString = implode('<br/>', $lines);

            $view->registerJs('toastr.error(\'' . $errorsString . '\');');
        }
    }
}
