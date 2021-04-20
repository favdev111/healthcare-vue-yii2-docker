<?php

namespace modules\account\controllers\backend;

use modules\account\models\backend\Account;
use modules\account\models\backend\AccountStudentSearch;
use backend\components\controllers\Controller;
use modules\account\models\forms\patient\PatientUpdateForm;
use modules\account\models\search\JobSearch;
use yii\web\NotFoundHttpException;
use modules\account\actions\EditableAction;
use Yii;
use yii\web\Response;

/**
 * DefaultController implements the CRUD actions for Account model.
 */
class PatientController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'editable' => [
                'class' => EditableAction::class,
                'model' => Account::class,
            ],
        ];
    }

    /**
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AccountStudentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
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
        $searchModel = new JobSearch(['accountId' => $id]);
        $dataProvider = $searchModel->backendSearch(Yii::$app->request->queryParams);

        Yii::$app->user->setReturnUrl(Yii::$app->request->url);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = Yii::createObject(PatientUpdateForm::class, [
            $this->findModel($id)
        ]);

        if ($model->load($this->request->post()) && ($account = $model->save()) !== null) {
            return $this->redirect(['patient/view', 'id' => $account->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Account model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Account the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Account::findOneWithoutRestrictions($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
