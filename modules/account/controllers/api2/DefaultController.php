<?php

namespace modules\account\controllers\api2;

use api2\helpers\DoctorType;
use api2\helpers\EnrolledTypes;
use api2\helpers\PlatformHelper;
use api2\helpers\ProfessionalType;
use Codeception\Util\HttpCode;
use common\helpers\AccountStatusHelper;
use modules\account\models\AccountAccessToken;
use modules\account\models\api2\Account;
use modules\account\models\api2\forms\PasswordRecovery;
use modules\account\models\api2\forms\PasswordReset;
use modules\account\models\api2\forms\pusherAuth\PusherAuthForm;
use modules\account\models\api2\forms\SignUpSpecialist;
use modules\account\models\api2\forms\UpdatePassword;
use modules\account\models\api2\forms\UpdateSpecialist;
use modules\account\models\api2\Profile;
use common\helpers\Role;
use modules\account\actions\LoginAction;
use modules\account\helpers\ConstantsHelper;
use modules\account\models\EducationDegree;
use modules\account\responses\AccountResponse;
use modules\notification\models\notifications\AccountVerificationSpecialistNotification;
use modules\notification\models\notifications\ForgotPasswordNotification;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
                'actions' => ['me', 'signout', 'password-update', 'confirm', 'pusher-auth'],
                'allow' => true,
                'roles' => [Role::ROLE_SPECIALIST, Role::ROLE_PATIENT],
            ],
            [
                'allow' => true,
                'roles' => [Role::ROLE_SPECIALIST],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $account = $this->module->modelStatic('Account');
        return [
            'signin' => [
                'class' => LoginAction::class,
                'roles' => [Role::ROLE_SPECIALIST, Role::ROLE_PATIENT],
                'accountQuery' => $account::find(),
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
            'signin',
            'signup',
            'resend-confirmation',
            'password-recovery',
            'password-reset',
            'constants',
        ];
        $behaviors['authenticator']['except'] = [
            'signin',
            'signup',
            'resend-confirmation',
            'password-recovery',
            'password-reset',
            'constants',
        ];
        return $behaviors;
    }

    public function actionSignout()
    {
        $deviceToken = $this->getDeviceToken();
        $account = Yii::$app->user->identity;
        $userAccessToken = $account->getAccessTokens()->andWhere(['deviceToken' => $deviceToken])->limit(1)->one();
        if ($userAccessToken) {
            $userAccessToken->delete();
        }

        // Setting status code to 204 since this action returns no response.
        Yii::$app->response->statusCode = 204;
    }

    public function actionPasswordRecovery()
    {
        $form = new PasswordRecovery();
        $form->load($this->request->post());

        if ($form->validate()) {
            $account = Account::find()->andNonSuspended()->andWhere(['email' => $form->email])->one();
            if ($account) {
                $token = $account->generateToken();
                Yii::$app->notifier->send(
                    $account,
                    Yii::createObject([
                        'class' => ForgotPasswordNotification::class,
                        'token' => $token->token,
                    ])
                );
            }
        }

        Yii::$app->response->statusCode = 204;
    }

    public function actionPasswordReset(string $token)
    {
        /** @var \modules\account\models\Token $userToken */

        $userToken = $this->module->model('Token');
        $userToken = $userToken::findByToken($token, $userToken::TYPE_PASSWORD_RESET);
        $form = new PasswordReset();
        if (!$userToken) {
            $form->addError('token', 'The token is already used. Create a new reset password request.');
            return $form;
        }

        $form->load($this->request->post());
        if (!$form->validate()) {
            return $form;
        }

        $user = $userToken->account;
        $user->newPassword = $form->password;
        if ($user->save()) {
            $userToken->markAsUsed();
        }

        Yii::$app->response->statusCode = 204;
    }

    public function actionMe()
    {
        return new AccountResponse(Yii::$app->user->identity);
    }

    public function actionPasswordUpdate()
    {
        $data = Yii::$app->request->post();
        $account = Yii::$app->user->identity;
        $form = new UpdatePassword(['passwordHash' => $account->passwordHash]);
        $form->load($data);
        if (!$form->validate()) {
            return $form;
        }

        $account->newPassword = $form->password;
        $account->save(false);

        Yii::$app->response->statusCode = 204;
    }

    protected function getConfirmationToken($account)
    {
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
            $accessToken->save(false);
        }
        return $accessToken;
    }

    public function actionResendConfirmation()
    {
        $email = Yii::$app->request->post('email') ?? null;
        if (empty($email)) {
            throw new NotFoundHttpException();
        }

        $account = Account::find()
            ->andWhere(['email' => $email])
            ->andWhere(['isEmailConfirmed' => false])
            ->limit(1)
            ->one();

        if (empty($account)) {
            throw new NotFoundHttpException();
        }

        $accessToken = $this->getConfirmationToken($account);
        Yii::$app->notifier->send(
            $account,
            Yii::createObject([
                'class' => AccountVerificationSpecialistNotification::class,
                'token' => $accessToken->token,
            ])
        );

        Yii::$app->response->statusCode = 204;
    }


    public function actionSignup()
    {
        $data = Yii::$app->request->post();
        $form = new SignUpSpecialist();
        $form->load($data, '');
        if (!$form->validate()) {
            return $form;
        }

        $account = new Account();
        $account->roleId = Role::ROLE_SPECIALIST;
        $account->load($data, '');
        $account->save(false);
        $account->refresh();

        $profile = new Profile(['accountId' => $account->id]);
        $profile->load($data, '');
        $profile->save(false);

        /**
         * @var $accessToken AccountAccessToken
         */
        $accessToken = $this->getConfirmationToken($account);
        $accessToken->save(false);
        Yii::$app->notifier->send(
            $account,
            Yii::createObject([
                'class' => AccountVerificationSpecialistNotification::class,
                'token' => $accessToken->token,
            ])
        );

        return new AccountResponse($account);
    }

    public function actionConstants()
    {
        $result = [];
        $array = [
            'gender' => ConstantsHelper::gender(),
            'genderPatient' => ConstantsHelper::genderPatient(),
            'maritalStatus' => ConstantsHelper::maritalStatus(),
            'educationLevel' => ConstantsHelper::educationLevel(),
            'doctorTypes' => DoctorType::DOCTORS_SPECIALIZATION_LABELS,
            'nurseTypes' => DoctorType::NURSE_SPECIALIZATION_LABELS,
            'professionalType' => ProfessionalType::LABELS,
            'medicareMedicaid' => EnrolledTypes::LABELS,
            'degrees' => array_column(EducationDegree::find()->all(), 'name', 'id'),
        ];

        foreach ($array as $key => $item) {
            $result[$key] = ConstantsHelper::convertArray($item);
        }

        return $result;
    }

    public function actionConfirm()
    {
        $account = Yii::$app->user->identity;
        if ($account->isPatient()) {
            $request = Yii::$app->request;
            $recaptcha = new \ReCaptcha\ReCaptcha(env('GOOGLE_RECAPTCHA_SECRET_KEY'));
            $response = $recaptcha
                ->setExpectedHostname(parse_url(env('FRONTEND_URL'), \PHP_URL_HOST))
                ->verify($request->post('recaptchaToken'), $request->remoteIP);

            if (!$response->isSuccess()) {
                throw new ForbiddenHttpException('Please verify you are a human.');
            }

            $account->status = AccountStatusHelper::STATUS_ACTIVE;
        }

        $account->isEmailConfirmed = true;

        $account->save(false);
        $account->refresh();

        return new AccountResponse($account);
    }

    public function actionConfig()
    {
        return [];
    }

    public function actionNewDeviceToken()
    {
        $deviceToken = $this->getDeviceToken();
        $newDeviceToken = Yii::$app->request->post('token');

        if (!PlatformHelper::checkDeviceToken($newDeviceToken, Yii::$app->request->getPlatform())) {
            throw new InvalidArgumentException('Device token required');
        }

        $account = Yii::$app->user->identity;
        $userAccessToken = $account->getAccessTokens()->andWhere(['deviceToken' => $deviceToken])->limit(1)->one();
        if (!$userAccessToken) {
            throw new NotFoundHttpException();
        }

        $userAccessToken->deviceToken = $newDeviceToken;
        $userAccessToken->save();

        // Setting status code to 204 since this action returns no response.
        Yii::$app->response->statusCode = 204;
    }

    /**
     * @return mixed
     * @throws \Pusher\PusherException|\yii\base\InvalidConfigException
     */
    public function actionPusherAuth()
    {
        Yii::$app->setComponents([
            'response' => [
                'class' => Response::class,
                'format' => Response::FORMAT_JSON,
            ],
        ]);

        $model = Yii::createObject(PusherAuthForm::class, [
            $this->currentAccount,
            Yii::$app->pusher
        ]);

        $data = [
            'channelName' => $this->request->post('channel_name'),
            'socketId' => $this->request->post('socket_id'),
        ];

        if ($model->load($data) && ($authData = $model->auth()) !== null) {
            return $authData;
        }

        throw new BadRequestHttpException();
    }

    /**
     * @return Account|UpdateSpecialist|object|null
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate()
    {
        $model = Yii::createObject(UpdateSpecialist::class, [
            $this->currentAccount
        ]);

        if (!$model->load($this->request->post())) {
            throw new BadRequestHttpException('At least one field must be set');
        }

        if (($account = $model->update()) !== null) {
            return $account->response;
        }

        return $model;
    }
}
