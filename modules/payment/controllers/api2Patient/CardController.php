<?php

namespace modules\payment\controllers\api2Patient;

use common\helpers\Role;
use modules\payment\models\api2Patient\forms\CreateCardForm;
use modules\payment\models\CardInfo;
use Yii;
use yii\web\NotFoundHttpException;

class CardController extends \api2\components\RestController
{
    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [
            [
                'actions' => ['create', 'index', 'set-active', 'delete'],
                'allow' => true,
                'roles' => [Role::ROLE_PATIENT],
            ],
        ];
    }

    public function actionIndex()
    {
        return Yii::$app->user->identity->cardInfo;
    }

    public function actionCreate()
    {
        $form = new CreateCardForm();
        $form->load(Yii::$app->request->post(), '');
        if (!$form->validate()) {
            return $form;
        }

        try {
            foreach ($form->paymentCardTokens as $cardToken) {
                Yii::$app->payment->attachCardToCustomer(
                    $cardToken,
                    Yii::$app->user->identity,
                    in_array($form->activeCardToken, $form->paymentCardTokens)
                );
            }
        } catch (\Throwable $exception) {
            $form->addError('paymentCardTokens', $exception->getMessage());
            return $form;
        }

        Yii::$app->user->identity->refresh();

        return Yii::$app->user->identity->cardInfo;
    }

    public function actionSetActive()
    {
        $id = (int)Yii::$app->request->post('id');
        if (empty($id)) {
            throw new NotFoundHttpException();
        }

        $account = Yii::$app->user->identity;
        $paymentCustomer = $account->paymentCustomer ?? null;
        if (empty($paymentCustomer)) {
            throw new NotFoundHttpException();
        }

        /**
         * @var $card CardInfo
         */
        $card = CardInfo::find()
            ->andWhere(['stripeCustomerId' => $paymentCustomer->id])
            ->andWhere(['id' => $id])
            ->limit(1)
            ->one();

        if (empty($card)) {
            throw new NotFoundHttpException();
        }

        CardInfo::updateAll(['active' => false], ['stripeCustomerId' => $paymentCustomer->id]);
        $card->active = true;
        $card->save(false);
    }

    /**
     * @param int $id
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id)
    {
        if (Yii::$app->payment->removeCard($id, $this->currentAccount)) {
            $this->setNoContent();
        } else {
            throw new NotFoundHttpException();
        }
    }
}
