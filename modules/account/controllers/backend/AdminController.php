<?php

namespace modules\account\controllers\backend;

use backend\components\rbac\Rbac;
use Yii;
use backend\models\Account;
use yii\data\ActiveDataProvider;
use backend\components\controllers\Controller;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AdminController implements the CRUD actions for Account model.
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviorsAdd()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'actions' => ['create'],
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT],
            ],
            [
                'actions' => ['delete'],
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT],
                'matchCallback' => function ($rule, $action) {
                    $id = Yii::$app->request->getQueryParam('id', null);

                    if ($this->findModel($id)->isSuperAdmin()) {
                        return false;
                    }

                    return  $id != Yii::$app->user->identity->id;
                },
            ],
            [
                'actions' => ['update'],
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT],
                'matchCallback' => function ($rule, $action) {
                    $id = Yii::$app->request->getQueryParam('id', null);

                    return (($this->findModel($id)->isSuperAdmin() && !Yii::$app->user->identity->isSuperAdmin()) === false);
                },
            ],
            [
                'actions' => ['index', 'view'],
                'allow' => true,
                'roles' => [Rbac::PERMISSION_VIEW_ALL],
            ]
        ];

        return $behaviors;
    }

    /**
     * Lists all Account models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Account::find(),
            ]
        );

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Displays a single Account model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render(
            'view',
            [
                'model' => $this->findModel($id),
            ]
        );
    }

    /**
     * Creates a new Account model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Account();


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render(
                'create',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->user->can(Rbac::PERMISSION_VIEW_ALL)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->redirect(['/']);
        } else {
            return $this->render(
                'update',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Deletes an existing Account model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Account model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Account the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Account::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
