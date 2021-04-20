<?php

namespace backend\controllers;

use backend\components\rbac\Rbac;
use common\models\City;
use Yii;
use backend\components\controllers\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\LoginForm;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviorsAdd()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'error'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['cities-autocomplete'],
                        'allow' => true,
                        'roles' => [Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT, Rbac::PERMISSION_SEO_MANAGEMENT],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $formatter = Yii::$app->formatter;
        $model = Yii::$app->moduleAccount->getAccountModel(true);
        $modelJob = Yii::$app->moduleAccount->getJobModel(true);
        return $this->render(
            'index',
            [
                'doctorsActiveCount' => $formatter->asInteger($model::find()->isSpecialist()->active()->count()),
                'patientsActiveCount' => $formatter->asInteger($model::find()->isPatient()->active()->count()),
                'jobsActiveCount' => $formatter->asInteger(
                    $modelJob::find()->andWhere(['status' => $modelJob::PUBLISH])->count()
                ),
            ]
        );
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'main-login';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionCitiesAutocomplete($q)
    {
        $cities = City::find()->andWhere(['like', 'name', $q])->all();
        $data = [];
        foreach ($cities as $city) {
            $data[] = [
                'text' => '(' . $city->stateNameShort . ') ' . $city->name,
                'id' => $city->id,
            ];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['results' => $data];
    }
}
