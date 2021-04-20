<?php

namespace modules\account\controllers\api2Patient;

use Yii;
use common\helpers\Role;
use common\models\form\healthProfile\HealthProfileGeneralForm;
use modules\account\models\api2Patient\entities\healthProfile\HealthProfile;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class HealthProfileController extends \api2\components\ActiveController
{
    public $modelClass = HealthProfile::class;
    public $responseClass = \modules\account\responses\HealthProfile::class;

    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_PATIENT],
            ],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['update']);
        unset($actions['create']);
        unset($actions['view']);

        $actions['index']['prepareDataProvider'] = function ($action) {
            $modelClass = $this->modelClass;

            return new ActiveDataProvider([
                'query' => $modelClass::find()
                    ->active()
                    ->andWhere(['accountId' => Yii::$app->user->id]),
            ]);
        };

        return $actions;
    }

    public function actionCreate()
    {
        $form = new HealthProfileGeneralForm([
            'scenario' => 'create',
        ]);

        $form->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($form->validate()) {
            $model = new $this->modelClass();
            $model->accountId = $this->getCurrentAccount()->id;
            $model->setAttributes(
                array_filter($form->getAttributes()),
                false
            );

            if (!$model->save(false)) {
                throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
            }

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));

            return $model;
        }

        return $form;
    }

    public function actionUpdate($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->findModel($id);
        $form = new HealthProfileGeneralForm([
            'model' => $model,
        ]);

        $data = Yii::$app->getRequest()->post();
        $form->load($data, '');
        if ($form->validate()) {
            $model->setAttributes(
                array_filter($form->getAttributes()),
                false
            );

            if (!$model->save(false)) {
                throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
            }
        } else {
            return $form;
        }

        return $model;
    }

    /**
     * Displays a model.
     * @param string $id the primary key of the model.
     * @return \yii\db\ActiveRecordInterface the model being displayed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $model;
    }

    protected function findModel($id)
    {
        /* @var $modelClass ActiveRecordInterface */
        $modelClass = $this->modelClass;
        if ($id !== null) {
            $model = $modelClass::find()
                ->andWhere(['id' => $id])
                ->andWhere(['accountId' => Yii::$app->user->id])
                ->limit(1)
                ->one();
        }

        if (isset($model)) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found: $id");
    }
}
