<?php

namespace backend\controllers;

use backend\components\controllers\Controller;
use modules\account\models\AutomatchSubject;
use modules\account\models\search\AutomatchSubjectSearch;
use yii\web\NotFoundHttpException;

class AutomatchSubjectsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new AutomatchSubjectSearch();
        $searchModel->load(\Yii::$app->request->get());
        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $searchModel->search(\Yii::$app->request->post()),
            ]
        );
    }

    public function actionCreate()
    {
        $automatchSubject = new AutomatchSubject();
        $automatchSubject->load(\Yii::$app->request->post(), '');
        $automatchSubject->save();
        return $this->redirect(['automatch-subjects/index']);
    }

    public function actionDelete($id)
    {
        $model = AutomatchSubject::findOne($id);
        if (!empty($model)) {
            $model->delete();
        }
        return $this->redirect(['automatch-subjects/index']);
    }
}
