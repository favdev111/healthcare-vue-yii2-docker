<?php

namespace modules\account\controllers\backend;

use common\models\healthProfile\HealthProfile;
use modules\account\models\backend\Account;
use backend\components\controllers\Controller;
use modules\account\models\forms\healthProfile\search\HealthProfileSearch;
use modules\account\models\forms\patient\PatientUpdateForm;
use modules\account\models\search\JobSearch;
use yii\web\NotFoundHttpException;
use Yii;
use yii\web\Response;

/**
 * DefaultController implements the CRUD actions for Account model.
 */
class HealthProfileController extends Controller
{
    /**
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HealthProfileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $renderMethod = $this->request->isAjax ? 'renderAjax' : 'render';

        return $this->{$renderMethod}('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param $id
     * @return HealthProfile|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = HealthProfile::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
