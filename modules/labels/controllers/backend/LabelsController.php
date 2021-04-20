<?php

namespace modules\labels\controllers\backend;

use modules\labels\models\LabelsCategory;
use modules\labels\models\LabelStatus;
use Yii;
use modules\labels\models\Labels;
use modules\labels\models\search\LabelsSearch;
use backend\components\controllers\Controller;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * LabelsController implements the CRUD actions for Labels model.
 */
class LabelsController extends Controller
{
    /**
     * @return string
     */
    final public function actionIndex(): string
    {
        $searchModel = new LabelsSearch();
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
        $model = new Labels();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'statusList' => LabelStatus::getAvailableStatusList(),
            'categories' => ArrayHelper::map(LabelsCategory::find()->all(), 'id', 'name'),
        ]);
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
        }

        return $this->render('update', [
            'model' => $model,
            'statusList' => LabelStatus::getAvailableStatusList(),
            'categories' => ArrayHelper::map(LabelsCategory::find()->all(), 'id', 'name'),
        ]);
    }

    /**
     * @param int $id
     * @return Labels|null
     * @throws NotFoundHttpException
     */
    final private function findModel(int $id): ?Labels
    {
        if (($model = Labels::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
