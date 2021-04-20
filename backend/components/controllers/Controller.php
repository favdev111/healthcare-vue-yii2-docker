<?php

namespace backend\components\controllers;

use backend\components\rbac\Rbac;
use backend\components\widgets\ActiveForm;
use backend\models\BaseForm;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use Yii;
use yii\web\Response;

/**
 * Site controller
 *
 * @property-write string $errorMessage
 * @property-write string $successMessage
 */
class Controller extends \yii\web\Controller
{
    /**
     * Add custom filters
     *
     * @return array Like a behaviors function
     */
    public function behaviorsAdd()
    {
        return [];
    }

    /**
     * @param \yii\base\Action $action
     * @return bool|Response
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        try {
            return parent::beforeAction($action);
        } catch (ForbiddenHttpException $ex) {
            if (Yii::$app->request->referrer) {
                Yii::$app->session->setFlash('error', $ex->getMessage());
                Yii::$app->response->redirect(Yii::$app->request->referrer);
                return false;
            }
            throw new ForbiddenHttpException($ex->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $default = [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'], // add all actions to take guest to login page
                        'allow' => true,
                        'roles' => [Rbac::PERMISSION_VIEW_ALL],
                    ],
                    [
                        'allow' => true,
                        'roles' => [Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT],
                    ],
                    [
                        'allow' => true,
                        'roles' => [Rbac::PERMISSION_SEO_MANAGEMENT],
                        /**
                         * Yii::$app->controller->uniqueId
                         */
                        'controllers' => [
                            'account/subject',
                            'seo/static-page',
                            'account/review',
                        ],
                    ]
                ],
            ],
        ];

        return ArrayHelper::merge(
            $default,
            $this->behaviorsAdd()
        );
    }

    /**
     * @param string $message
     */
    public function setErrorMessage(string $message): void
    {
        Yii::$app->session->setFlash('error', $message);
    }

    /**
     * @param string $message
     */
    public function setSuccessMessage(string $message): void
    {
        Yii::$app->session->setFlash('success', $message);
    }

    /**
     * @return Response
     */
    public function refreshReferrer()
    {
        return $this->redirect($this->request->referrer);
    }

    /**
     * @param BaseForm $model
     * @return Response|null
     * @throws \Exception
     */
    public function ajaxValidation(BaseForm $model): ?\yii\web\Response
    {
        if ($this->request->isFormValidate) {
            $model->load(Yii::$app->request->post());
            return $this->asJson(ActiveForm::validate($model));
        }

        return null;
    }

    /**
     * @param $view
     * @param array $params
     * @return mixed
     */
    public function renderDetect($view, $params = [])
    {
        $renderMethod = $this->request->isAjax ? 'renderAjax' : 'render';

        return $this->{$renderMethod}($view, $params);
    }
}
