<?php

namespace modules\account\controllers\backend;

use modules\account\models\backend\Lesson;
use modules\account\models\backend\LessonSearch;
use modules\payment\models\backend\TransactionSearch;
use modules\payment\models\Transaction;
use Yii;
use backend\components\controllers\Controller;
use yii\web\NotFoundHttpException;

/**
 * LessonController implements the CRUD actions for Lesson model.
 */
class LessonController extends Controller
{

    /**
     * Lists all Lesson models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LessonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Yii::$app->user->setReturnUrl(Yii::$app->request->url);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Lesson model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $searchModel = new TransactionSearch([
            'objectId' => $id,
            'objectType' => Transaction::TYPE_LESSON,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Yii::$app->user->setReturnUrl(Yii::$app->request->url);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Lesson::findOne($id);
        if (empty($model)) {
            throw new \HttpException("Not found", 404);
        }

        if (
            Yii::$app->request->isPost
            && $model->load(Yii::$app->request->post())
        ) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', ['model' => $model]);
    }


    /**
     * Finds the Lesson model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Lesson the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Lesson::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
