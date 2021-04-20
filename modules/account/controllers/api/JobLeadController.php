<?php

namespace modules\account\controllers\api;

use modules\account\models\JobLead;
use Yii;
use yii\rest\CreateAction;

class JobLeadController extends \api\components\Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'create' => [
                'class' => CreateAction::class,
                'modelClass' => JobLead::class,
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
}
