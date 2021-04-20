<?php

namespace modules\account\controllers\api;

use modules\account\models\forms\TutorForm;
use Yii;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * Default controller for User module
 */
class AuthController extends \api\components\Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    public function actionSignup()
    {
    }
}
