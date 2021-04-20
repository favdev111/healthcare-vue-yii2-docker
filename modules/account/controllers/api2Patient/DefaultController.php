<?php

namespace modules\account\controllers\api2Patient;

use modules\account\helpers\SignupHelper;
use modules\account\models\AccountAccessToken;
use modules\account\models\AccountSocialAuth;
use modules\account\models\api2\Account;
use modules\account\models\api2\Profile;
use common\helpers\Role;
use modules\account\models\api2Patient\forms\SignUp;
use modules\account\models\api2Patient\forms\Update;
use modules\account\responses\AccountResponse;
use modules\notification\models\notifications\AccountVerificationPatientNotification;
use Yii;

/**
 * Default controller for Account actions
 */
class DefaultController extends \api2\components\RestController
{
    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_PATIENT],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = [
            'signup',
            'options',
        ];
        $behaviors['authenticator']['except'] = [
            'signup',
            'options',
        ];
        return $behaviors;
    }

    public function actionMeUpdate()
    {
        $data = Yii::$app->request->post();
        $account = Yii::$app->user->identity;
        $form = new Update(['accountId' => $account->id]);
        $form->load($data);
        if (!$form->validate()) {
            return $form;
        }

        $account->setAttributes($form->attributes, false);
        $account->save(false);
        $account->refresh();

        $profile = $account->profile;
        $profile->setAttributes($form->attributes, false);
        $profile->save(false);

        return new AccountResponse($account);
    }

    public function actionSignup()
    {
        $data = Yii::$app->request->post();
        $form = new SignUp();
        $form->load($data);
        if (!$form->validate()) {
            return $form;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $account = new Account();
            $account->roleId = Role::ROLE_PATIENT;
            $account->setAttributes($data, $form->attributes);
            $account->save(false);
            $account->refresh();

            $profile = new Profile();
            $profile->setAttributes($data, $form->attributes);
            $account->link('profile', $profile);

            $mainHealthProfile = $account->mainHealthProfile;
            $mainHealthProfile->setAttributes([
                'firstName' => $profile->firstName,
                'lastName' => $profile->lastName,
                'phoneNumber' => $profile->phoneNumber,
                'email' => $account->email,
            ], false);
            $mainHealthProfile->save(false);

            $this->addSocial($form, $account);

            $platformId = Yii::$app->request->getPlatform();
            $deviceToken = Yii::$app->request->getDeviceToken();
            /**
             * @var $accessToken AccountAccessToken
             */
            $accessToken = $account->getAccessTokens()->andWhere(['deviceToken' => $deviceToken])->limit(1)->one();
            if (!$accessToken) {
                $accessToken = new AccountAccessToken();
                $accessToken->accountId = $account->id;
                $accessToken->platform = $platformId;
                $accessToken->deviceToken = $deviceToken;
            } else {
                // Create new token
                $accessToken->generateNewToken();
            }

            $accessToken->save(false);

            Yii::$app->notifier->send(
                $account,
                Yii::createObject([
                    'class' => AccountVerificationPatientNotification::class,
                    'token' => $accessToken->token,
                ])
            );

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        Yii::$app->payment->createPaymentCustomer($account);

        return new AccountResponse($account);
    }

    protected function addSocial(SignUp $form, Account $account): void
    {
        if (!$form->socialAccessToken || !$form->socialId || !$form->socialUserId) {
            return;
        }

        $socialData = SignupHelper::getSocialData($form->socialId, $form->socialAccessToken);
        if (!$socialData) {
            return;
        }

        if ((string)$socialData['id'] !== $form->socialUserId) {
            return;
        }

        $model = new AccountSocialAuth();
        $model->setAttributes([
            'source' => $form->socialId,
            'sourceId' => $form->socialUserId,
        ], false);
        $account->link('socialAuths', $model);
    }
}
