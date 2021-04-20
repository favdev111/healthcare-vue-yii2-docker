<?php

namespace modules\account\controllers\api2Patient;

use common\models\Lead;
use modules\account\models\api2Patient\forms\SignUpLead;

class LeadController extends \api2\components\RestController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = [
            'signup',
            'options',
        ];
        $behaviors['authenticator']['except'] = [
            'signup',
            'options',
        ];
        return $behaviors;
    }

    public function actionSignup()
    {
        $form = new SignUpLead();
        $form->load($this->request->post());
        if (!$form->validate()) {
            return $form;
        }

        $relations = $form->getRelationModels();

        $lead = new Lead();
        $lead->setAttributes(
            [
                'name' => $form->name,
                'phoneNumber' => $form->phone_number,
                'email' => $form->email,
                'data' => [
                    'relations' => array_map(function ($model) {
                        return [
                            'id' => $model->id,
                            'name' => $model->name,
                        ];
                    }, $relations),
                ],
                'source' => Lead::SOURCE_SIGNUP_FREE_HEALTH_CONSULTATION,
                'advertisingChannel' => Lead::ADVERTISING_CHANNEL_ORGANIC,
                'clickId' => '',
                'ip' => \Yii::$app->request->getUserIP(),
            ],
            false
        );

        $lead->save(false);

        $this->response->statusCode = 204;

        return null;
    }

    protected function processMultipleModels(array $data, string $type, string $className)
    {
        $models = [];
        foreach ($data as $item) {
            if ($item['type'] !== $type) {
                continue;
            }

            $model = new $className();
            $model->id = $item['id'];
            $models[] = $model;
        }

        return $models;
    }
}
