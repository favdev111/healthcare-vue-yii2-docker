<?php

namespace modules\labels\controllers\backend;

use Yii;
use modules\labels\models\LabelsCategory;
use modules\labels\models\search\LabelsCategorySearch;
use backend\components\controllers\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CategoriesController implements the CRUD actions for LabelsCategory model.
 */
class CategoriesController extends Controller
{

    /**
     * @return string
     */
    final public function actionIndex(): string
    {
        $searchModel = new LabelsCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    final public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * @return string|Response
     */
    final public function actionCreate()
    {
        $model = new LabelsCategory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    final public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * @param int $id
     * @return LabelsCategory|null
     * @throws NotFoundHttpException
     */
    final private function findModel(int $id): ?LabelsCategory
    {
        if (($model = LabelsCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
