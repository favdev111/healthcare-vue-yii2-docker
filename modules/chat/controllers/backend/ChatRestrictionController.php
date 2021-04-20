<?php

namespace modules\chat\controllers\backend;

use Yii;
use modules\chat\models\ChatRestriction;
use modules\chat\models\ChatRestrictionSearch;
use backend\components\controllers\Controller;
use yii\web\NotFoundHttpException;

/**
 * ChatRestrictionController implements the CRUD actions for ChatRestriction model.
 */
class ChatRestrictionController extends Controller
{

    /**
     * Lists all ChatRestriction models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ChatRestrictionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ChatRestriction model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing ChatRestriction model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->cacheConsole->delete(ChatRestriction::CACHE_KEY);
            Yii::$app->cache->delete(ChatRestriction::CACHE_KEY);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the ChatRestriction model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ChatRestriction the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ChatRestriction::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
