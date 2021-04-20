<?php

namespace modules\account\controllers\console;

use common\models\PostPayment;
use modules\notification\helpers\NotificationHelper;
use modules\notification\models\Notification;
use yii\console\Controller;
use yii\helpers\Console;

class PostPaymentController extends Controller
{
    public function actionCreatePostPaymentTransactions()
    {
        $today = date(PostPayment::DATE_FORMAT, time());
        $models = PostPayment::find()->andWhere(['date' => $today])->andWhere(['status' => PostPayment::STATUS_NOT_RECEIVED])->all();
        if ($models) {
            /**
             * @var PostPayment $model
             */
            foreach ($models as $model) {
                $activeCard = $model->account->paymentCustomer->activeCard;
                if (empty($activeCard)) {
                    $notify = new Notification();
                    $notify->type = NotificationHelper::TYPE_POST_PAYMENT_SHOULD_PAY;
                    $notify->extraData = [
                        'postPaymentId' => $model->id,
                        'studentId' => $model->accountId
                    ];
                    $notify->accountId = $model->account->id;
                    $notify->pin(NotificationHelper::OBJECT_TYPE_POST_PAYMENT, $model->id); /*pin post-payment notification*/
                    $notify->save(false);
                    Console::output("Can not create Transaction for post payment with id = $model->id. Client with id = " . $model->account->id . " doesn't have an active card.");
                    continue;
                }
                if ($transactionId = $model->createTransaction()) {
                    Console::output('Transaction with id = ' . $transactionId
                        . "\nsuccessfully created for Post Payment with id = $model->id");
                } else {
                    Console::output('Failed to create transaction for post payment with id  = ' . $model->id);
                }
            }
        }
    }
}
