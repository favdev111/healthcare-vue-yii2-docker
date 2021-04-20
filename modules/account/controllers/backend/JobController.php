<?php

namespace modules\account\controllers\backend;

use common\helpers\Role;
use modules\account\models\backend\Job;
use modules\account\models\JobSubject;
use modules\account\models\search\JobSearch;
use modules\account\models\Subject;
use Yii;
use backend\components\controllers\Controller;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * JobController implements the CRUD actions for Job model.
 */
class JobController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviorsAdd()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['check-autogenerate-new-job'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Job models.
     * @return mixed
     */
    public function actionIndex($autogenerate = false)
    {
        $searchModel = new JobSearch();
        $dataProvider = $searchModel->backendSearch(Yii::$app->request->queryParams, $autogenerate);

        Yii::$app->user->setReturnUrl(Yii::$app->request->url);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'autogenerate' => $autogenerate,
        ]);
    }

    public function actionSetViewedJob()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $jobViewedIds = Yii::$app->session->get('jobViewed', []);
        $jobViewedIds = json_decode($jobViewedIds);
        if (!empty($jobViewedIds)) {
            Job::updateAll(['updatedAt' => new Expression('NOW()'), 'viewed' => true], ['id' => $jobViewedIds]);
            return [
                'count' => count($jobViewedIds),
            ];
        } else {
            return [
                'count' => 0,
            ];
        }
    }

    public function actionCheckAutogenerateNewJob()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return [
                'count' => 0,
            ];
        }
        $jobs = Job::find()->andWhere(['autogenerate' => true, 'status' => Job::UNPUBLISH, 'viewed' => false])->asArray()->all(); //all unpublish job
        $jobViewedIds = array_column($jobs, 'id');
        Yii::$app->session->set('jobViewed', json_encode($jobViewedIds));
        return [
            'count' => count($jobs),
            'ids' => $jobViewedIds
        ];
    }

    public function actionUpdate($id)
    {
        $job = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $job->load($data);
            $job->status = Job::PUBLISH; //publish
            if ($job->save()) {
                return $this->redirect(['/account/job/view/', 'id' => $id]);
            }
        }
        if ($job->subjects) {
            $curSubjects = array_column(Subject::find()->andWhere(['in', 'id', $job->subjects])->indexBy('id')->all(), 'name', 'id');
        } else {
            $curSubjects = array_column($job->getSubjects()->indexBy('id')->all(), 'name', 'id');
        }
        return $this->render('update', [
            'job' => $job,
            'curSubjects' => $curSubjects ? $curSubjects : [],
        ]);
    }

    /**
     * Displays a single Job model.
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
     * Deletes an existing Job model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//        // TODO: Notify student that job is deleted
//        return $this->goBack(['index']);
//    }

    /**
     * Blocks an existing Job model.
     * If block is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionBlock($id)
    {
        $job = $this->findModel($id);
        $job->block = true;

        // Copied according to Roman Efimenko.
        $curSubjectsIds = array_keys($job->getJobSubjects()->indexBy('subjectId')->all());
        $job->subjects = $curSubjectsIds;
        // End of copy

        $job->detachBehavior('blamebale');
        $job->save(['block']);
        // TODO: Notify student that job is blocked
        return $this->goBack(['index']);
    }

    /**
     * Unblocks an existing Job model.
     * If block is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUnblock($id)
    {
        $job = $this->findModel($id);
        $job->block = false;

        // Copied according to Roman Efimenko.
        $curSubjectsIds = array_keys($job->getJobSubjects()->indexBy('subjectId')->all());
        $job->subjects = $curSubjectsIds;
        // End of copy

        $job->detachBehavior('blamebale');
        $job->save(['block']);
        // TODO: Notify student that job is unblocked
        return $this->goBack(['index']);
    }

    /**
     * Finds the Job model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Job the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Job::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
