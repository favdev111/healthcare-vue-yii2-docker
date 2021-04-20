<?php

namespace modules\payment\controllers\backend;

use common\helpers\QueueHelper;
use common\models\RefundData;
use modules\payment\models\backend\TransactionSearch;
use modules\payment\models\Transaction;
use modules\payment\models\TransactionBalance;
use modules\payment\models\TransactionEnterpriseSearch;
use Yii;
use backend\components\controllers\Controller;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * TransactionController implements the CRUD actions for Transaction model.
 */
class TransactionController extends Controller
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
                    'refund' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Transaction models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransactionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Yii::$app->user->setReturnUrl(Yii::$app->request->url);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Transaction model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function getSearchPartialRefund($model)
    {
        $searchPartialRefunds = new TransactionEnterpriseSearch();
        $searchPartialRefunds->excludePartialRefund(false);
        $searchPartialRefunds->type = Transaction::PARTIAL_REFUND;
        $searchPartialRefunds->parentId = $model->id;
        return $searchPartialRefunds;
    }

    public function actionRefund($id)
    {
        $refundData = new RefundData();
        $refundData->transactionId = $id;
        if (!$refundData->refund()) {
            Yii::$app->session->setFlash('error', 'Failed to refund transaction');
        } else {
            Yii::$app->session->setFlash('success', 'Transaction refunded successfully.');
        }
        $this->goBack(['index']);
    }

    /**
     * Aprrove a single Transaction.
     * @param integer $id
     * @return mixed
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        if ($model->isWaitingForApprove === false) {
            Yii::$app->session->addFlash('error', 'Transaction is not waiting for approve.');
            return $this->redirect(Yii::$app->request->getReferrer());
        }

        $model->setStatusNew()->save(false);
        Yii::$app->session->addFlash('success', 'Transaction approved successfully.');
        QueueHelper::sendLessonEmail($model->lesson->id);
        return $this->redirect(Yii::$app->request->getReferrer());
    }

    /**
     * Finds the Transaction model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Transaction the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = \modules\payment\models\backend\Transaction::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionRecharge($id)
    {
        $oldTransaction = Transaction::findOne($id);

        if (empty($oldTransaction)) {
            Yii::$app->session->addFlash('error', 'Transaction does not exist');
        } else if (Transaction::reCharge($oldTransaction)) {
            Yii::$app->session->addFlash('success', 'This transaction has been re-started.');
        } else {
            Yii::$app->session->addFlash('error', 'Can not re-start this transaction.');
        }
        return $this->redirect(Yii::$app->request->getReferrer());
    }

    /**
     * Reject a single Transaction.
     * @param integer $id
     * @return mixed
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        if ($model->isWaitingForApprove === false) {
            Yii::$app->session->addFlash('error', 'Transaction is not waiting for approve.');
            return $this->redirect(Yii::$app->request->getReferrer());
        }

        $dbTransaction = Yii::$app->db->beginTransaction();

        if ($model->updateStatusRejected()) {
            $dbTransaction->commit();
            Yii::$app->session->addFlash('success', 'Transaction rejected successfully.');
        } else {
            $dbTransaction->rollBack();
            Yii::$app->session->addFlash('error', 'Failed to reject transaction...');
        }
        return $this->redirect(Yii::$app->request->getReferrer());
    }
}
