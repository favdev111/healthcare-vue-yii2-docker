<?php

namespace api2\controllers;

use api2\components\RestController;
use modules\account\models\api2\Language;

class LanguageController extends RestController
{
    public function behaviors()
    {
        return [
            'pageCache' => [
                'class' => \yii\filters\PageCache::class,
                'duration' => \Yii::$app->params['cachePageDuration'],
                'only' => ['index'],
                'variations' => [
                    \Yii::$app->request->pathInfo,
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return Language::find()->all();
    }
}
