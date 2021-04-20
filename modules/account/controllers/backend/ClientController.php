<?php

namespace modules\account\controllers\backend;

use modules\account\models\api\AccountClient;
use modules\account\models\forms\ProfileClientForm;
use Yii;
use modules\account\models\backend\Account;
use modules\account\models\backend\AccountClientSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ClientController implements the CRUD actions for Account model.
 */
class ClientController extends PatientController
{

    public function actionIndex()
    {
        $searchModel = new AccountClientSearch();
        if (empty($searchModel->status)) {
            $searchModel->status = Account::STATUS_ACTIVE;
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    protected function findModel($id)
    {
        $model = parent::findModel($id);
        if (!$model->isPatient()) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        $account = $this->findModel($id);

        /** @var \modules\account\models\forms\ProfileClientForm $model */
        $model = $this->module->model('ProfileClientForm');
        $model->scenario = 'update';
        $modelLoad = $model->load(Yii::$app->request->post());
        $model->accountModel = $account;

        if ($modelLoad) {
            $model->emails = Yii::$app->request->post('AccountEmail');
            $model->phoneNumbers = Yii::$app->request->post('AccountPhone');
            if ($model->update()) {
                return $this->redirect(['view', 'id' => $account->id]);
            }
        } else {
            $model->load($account->attributes + $account->profile->attributes, '');
        }

        $model->phoneNumbers = array_reverse($account->accountPhones);
        $model->emails = array_reverse($account->accountEmails);
        return $this->render(
            'update',
            [
                'model' => $model,
                'account' => $account,
            ]
        );
    }

    public function actionPdf($id)
    {
        $client = $this->findModel($id);
        if (empty($client)) {
            throw new NotFoundHttpException();
        }
        $params = [
            'mimeType' => 'application/pdf',
            'xHeader' => 'X-Accel-Redirect',
            'inline' => true,
        ];
        $filePath = '/uploads/signaturePdf/' . $client->id . '.pdf';
        $profile = $client->profile;
        return Yii::$app->response->xSendFile(
            $filePath,
            "Terms of Use {$profile->fullName()}.pdf",
            $params
        );
    }
}
