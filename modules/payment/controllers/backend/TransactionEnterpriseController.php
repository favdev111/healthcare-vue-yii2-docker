<?php

namespace modules\payment\controllers\backend;

use common\models\RefundData;
use modules\account\models\backend\Lesson;
use modules\payment\models\backend\TransactionSearch;
use modules\payment\models\Transaction;
use modules\payment\models\TransactionBalance;
use modules\payment\models\TransactionEnterpriseSearch;
use Yii;
use backend\components\controllers\Controller;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * TransactionController implements the CRUD actions for Transaction model.
 */
class TransactionEnterpriseController extends TransactionController
{


    public function actionIndex()
    {
        $searchModel = new TransactionEnterpriseSearch();
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
        $model = $this->findModel($id);
        $refundData = new RefundData();
        if ($model->isGroupChargeTransaction()) {
            $relatedTransfersProvider = new ActiveDataProvider([
                'query' => Transaction::find()->lessonBatchPayment()->whereGroupTransactionId($model->id),
                'sort' => [
                    'defaultOrder' => [
                        'createdAt' => SORT_DESC,
                    ],
                ],
            ]);
        }

        $searchPartialRefunds = $this->getSearchPartialRefund($model);
        $providerPartialRefunds = $searchPartialRefunds->search(Yii::$app->request->get());
        $viewParams = [
            'model' => $model,
            'providerPartialRefunds' => $providerPartialRefunds,
            'searchPartialRefunds' => $searchPartialRefunds,
            'refundData' => $refundData,
            'relatedTransfersProvider' => $relatedTransfersProvider ?? null,
        ];

        if (Yii::$app->request->post('RefundData')) {
            $refundData->load(Yii::$app->request->post());
            $refundData->transactionId = $model->id;
            return $this->refundProcess($refundData, $viewParams);
        }

        return $this->render('view', $viewParams);
    }

    public function refundProcess(RefundData $refundData, $viewParams)
    {
        if (!$refundData->refund()) {
            return $this->render('view', $viewParams);
        }
        Yii::$app->session->addFlash('success', 'Transaction refunded successfully.');
        return $this->goBack(['index']);
    }
}
