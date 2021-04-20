<?php

namespace backend\controllers;

use backend\components\controllers\Controller;
use backend\models\InsuranceCompany;
use backend\models\search\InsuranceCompanySearch;
use yii\web\NotFoundHttpException;

class InsuranceCompanyController extends Controller
{
    public function findModel($id): InsuranceCompany
    {
        $model = InsuranceCompany::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
    public function actionIndex()
    {
        $search = (new InsuranceCompanySearch());
        $provider = $search->search(\Yii::$app->request->post());
        return $this->render('index', [
            'search' => $search,
            'provider' => $provider,
        ]);
    }

    public function actionCreate()
    {
        $model = new InsuranceCompany();
        if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['index']);
            }
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(\Yii::$app->request->post())) {
            $model->save();
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        return $this->redirect(['index']);
    }
}
