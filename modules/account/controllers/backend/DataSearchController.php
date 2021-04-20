<?php

namespace modules\account\controllers\backend;

use modules\account\models\SearchData;
use modules\account\models\SearchDataSearch;
use Yii;
use backend\components\controllers\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class DataSearchController extends Controller
{
    /**
     * Lists all data search.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SearchDataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing FaqPost model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the FaqPost model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FaqPost the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SearchData::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
