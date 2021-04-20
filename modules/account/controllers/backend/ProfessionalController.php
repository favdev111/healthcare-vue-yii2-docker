<?php

namespace modules\account\controllers\backend;

use backend\components\rbac\Rbac;
use backend\models\BaseForm;
use modules\account\models\backend\ChangePasswordForm;
use modules\account\models\forms\professional\educationCertification\EducationCertificationForm;
use modules\account\models\forms\professional\ProfessionalUpdateForm;
use modules\account\models\forms\professional\role\ProfessionalRoleForm;
use modules\account\models\forms\professional\specifications\ProfessionalSpecificationsForm;
use modules\account\models\search\AccountNoteSearch;
use Yii;
use modules\account\actions\EditableAction;
use modules\account\models\Account;
use modules\account\models\backend\AccountProfessionalSearch;
use backend\components\controllers\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ProfessionalController
 * @package modules\account\controllers\backend
 */
class ProfessionalController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'editable' => [
                'class' => EditableAction::class,
                'model' => AccountProfessionalSearch::class,
            ],
            'editableNotes' => [
                'class' => EditableAction::class,
                'model' => AccountNoteSearch::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'index-details', 'view'], // add all actions to take guest to login page
                        'allow' => true,
                        'roles' => [Rbac::PERMISSION_VIEW_ALL],
                        'matchCallback' => function ($rule, $action) {
                            $isAdmin = Yii::$app->user->can(Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT);
                            return $isAdmin || Yii::$app->request->isGet;
                        },
                    ],
                    [
                        'allow' => true,
                        'roles' => [Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Account models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AccountProfessionalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        return $this->renderDetect('update', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionBasicInformation($id)
    {
        $model = Yii::createObject(ProfessionalUpdateForm::class, [
            $this->findModel($id)
        ]);

        return $this->handleUpdateForm($model, 'form/_basicInformation');
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRole($id)
    {
        $model = Yii::createObject(ProfessionalRoleForm::class, [
            $this->findModel($id)
        ]);

        return $this->handleUpdateForm($model, 'form/_role');
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSpecifications($id)
    {
        $model = Yii::createObject(ProfessionalSpecificationsForm::class, [
            $this->findModel($id)
        ]);

        return $this->handleUpdateForm($model, 'form/_specifications');
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEducationCertification($id)
    {
        $model = Yii::createObject(EducationCertificationForm::class, [
            $this->findModel($id)
        ]);

        return $this->handleUpdateForm($model, 'form/_educationAndCertification');
    }

    /**
     * @param BaseForm $model
     * @param string $view
     * @return mixed|Response|null
     * @throws \Exception
     */
    private function handleUpdateForm(BaseForm $model, string $view)
    {
        if (($result = $this->ajaxValidation($model)) !== null) {
            return $result;
        }

        if ($model->load($this->request->post()) && ($account = $model->save()) !== null) {
            return $this->redirect(['professional/view', 'id' => $account->id]);
        }

        return $this->renderDetect($view, [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Account model.
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */

    public function actionDelete($id)
    {
        $account = $this->findModel($id);
        if ($account->delete()) {
            $this->successMessage = "Health profile #{$account->id} deleted successfully";
        } else {
            $errorMessage = 'Failed to delete profile #' . $account->id;
            if ($account->hasErrors()) {
                $errorMessage .= ' - ' . reset($account->getFirstErrors());
            }
            $this->errorMessage = $errorMessage;
        }
        return $this->refreshReferrer();
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException|\yii\base\ErrorException
     */
    public function actionChangePassword($id)
    {
        $account = $this->findModel($id);

        $model = Yii::createObject(ChangePasswordForm::class, [$account]);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->change()) {
            $passwordChanged = true;
        }

        return $this->renderAjax('change-password', [
            'model' => $model,
            'passwordChanged' => $passwordChanged ?? false,
        ]);
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
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
