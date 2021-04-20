<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use InvalidArgumentException;
use modules\account\helpers\ConstantsHelper;
use modules\account\helpers\JobHelper;
use modules\account\models\Account;
use modules\account\models\AccountReturn;
use modules\account\models\api\AccountClient;
use modules\account\models\api\forms\ForgotForm;
use modules\account\models\api\ProfileClientSearch;
use modules\account\models\forms\InviteEmployeeForm;
use modules\account\models\forms\SignUpEmployeeForm;
use modules\account\models\Grade;
use modules\account\models\Token;
use modules\account\models\TutorSearch;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Default controller for User module
 */
class DefaultController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\Account';

    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'roles' => [Rbac::PERMISSION_CAN_MANAGE_EMPLOYEES],
                'actions' => ['invite-employee'],
            ],
            [
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BASE_B2B_PERMISSIONS],
                'actions' => [
                    'change-password',
                    'me',
                    'me-update',
                    'config',
                    'pusher-auth',
                ],
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset(
            $actions['index'],
            $actions['create'],
            $actions['update'],
            $actions['delete']
        );

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'] = [
            'login',
            'signup-employee',
            'forgot',
            'reset',
            'account-by-payment-token',
            'constants',
            'options',
            'confirm-email',
            'cancel-email',
        ];
        $behaviors['authenticator']['except'] = [
            'login',
            'forgot',
            'reset',
            'signup-employee',
            'constants',
            'account-by-payment-token',
            'options',
            'confirm-email',
            'cancel-email',
        ];

        $behaviors[] = [
            'class' => 'yii\filters\PageCache',
            'enabled' => !YII_ENV_DEV,
            'only' => ['constants', 'config'],
            'duration' => Yii::$app->params['cachePageDuration'],
        ];
        return $behaviors;
    }

    /**
     * @OA\Post(path="/accounts/confirm-email/",
     *    tags={"accounts"},
     *    summary="Confirm email change",
     *    description="",
     *    security={{"Bearer":{}}},
     *    @OA\RequestBody(
     *        @OA\Schema(
     *            type="object",
     *            @OA\Property(
     *                property="token",
     *                type="string",
     *                description="Token"
     *            )
     *        )
     *    ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionConfirmEmail()
    {
        $token = Yii::$app->request->post("token");
        if (empty($token)) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'token',
                    'message' => 'Token is empty',
                ],
            ];
        }
        /**
         * @var $userToken Token
         */
        $userToken = $this->module->model("Token");
        $userToken = $userToken::findByToken($token, [$userToken::TYPE_EMAIL_ACTIVATE, $userToken::TYPE_EMAIL_CHANGE]);
        if (!$userToken) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'token',
                    'message' => 'Token is invalid',
                ],
            ];
        }
        /**
         * @var $user Account
         */
        $user = $this->module->model("Account");
        $user = $user::findOne($userToken->accountId);
        if (empty($user->publicId)) {
            //TODO need to make field publicId NULL in database (publicId is empty by default for Company, it's  NOT NULL field in database)
            $user->publicId = Yii::$app->security->generateRandomString();
            $user->update(false, ['publicId']);
        }

        $newEmail = $userToken->getNewEmail();
        if (!$user->confirm($newEmail, $userToken->getOldEmail())) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'token',
                    'message' => 'Email is already exist',
                ],
            ];
        }

        $userToken->markAsUsed();
        return $user;
    }

    /**
     * @OA\Post(path="/accounts/cancel-email/",
     *    tags={"accounts"},
     *    summary="Cancel email change",
     *    description="",
     *    security={{"Bearer":{}}},
     *    @OA\RequestBody(
     *        @OA\Schema(
     *            type="object",
     *            @OA\Property(
     *                property="token",
     *                type="string",
     *                description="Token"
     *            )
     *        )
     *    ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionCancelEmail()
    {
        /** @var \modules\account\models\Account $user */
        /** @var \modules\account\models\Token $userToken */
        // find userToken of type email change
        $token = Yii::$app->request->post("token");
        if (empty($token)) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'token',
                    'message' => 'Token is empty',
                ],
            ];
        }
        $userToken = $this->module->model("Token");
        $userToken = $userToken::findByToken($token, $userToken::TYPE_EMAIL_CHANGE);
        if ($userToken) {
            $userToken->markAsUsed();
        } else {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'token',
                    'message' => 'Token is invalid',
                ],
            ];
        }
        $user = $this->module->model("Account");
        return $user::findOne($userToken->accountId);
    }

    /**
     * @OA\Post(path="/accounts/change-password/",
     *    tags={"accounts"},
     *    summary="Change password",
     *    description="",
     *    security={{"Bearer":{}}},
     *    @OA\RequestBody(
     *        @OA\Schema(
     *            type="object",
     *            @OA\Property(
     *                property="newPassword",
     *                type="string",
     *                description="New Password"
     *            ),
     *            @OA\Property(
     *                property="currentPassword",
     *                type="string",
     *                description="Old Password"
     *            )
     *        )
     *    ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionChangePassword()
    {
        $account = Yii::$app->user->identity;
        $account->scenario = 'changePassword';
        $account->load(Yii::$app->request->post(), '');
        $account->newPasswordConfirm = $account->newPassword;
        $account->save();

        return $account;
    }

    /**
     * @OA\Post(path="/accounts/forgot/",
     *     tags={"accounts"},
     *     summary="Forgot password",
     *     description="",
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 description="Email address"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="202", description=""),
     *     @OA\Response(response="422", description="")
     * )
     */
    public function actionForgot()
    {
        $response = Yii::$app->getResponse();

        $model = new ForgotForm();
        if (
            $model->load(Yii::$app->request->post(), '')
            && $model->sendForgotEmail()
        ) {
            $response->setStatusCode(202);
            return;
        }

        $model->validate();
        return $model;
    }

    /**
     * @OA\Post(path="/accounts/reset/{token}/",
     *     tags={"accounts"},
     *     summary="Reset password",
     *     description="",
     *     @OA\Parameter(
     *         description="Token to reset password",
     *         in="path",
     *         name="token",
     *         required=true,
     *         type="string"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="newPassword",
     *                 type="string",
     *                 description="Password"
     *             ),
     *             @OA\Property(
     *                 property="newPasswordConfirm",
     *                 type="string",
     *                 description="Old Password"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description=""),
     *     @OA\Response(response="422", description="")
     * )
     */

    public function actionReset($token)
    {
        /** @var \modules\account\models\Account $accountModel */
        /** @var \modules\account\models\Token $userToken */
        $userToken = $this->module->modelStatic('Token');
        $userToken = $userToken::findByToken($token, $userToken::TYPE_PASSWORD_RESET);
        if (!$userToken) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'token',
                    'message' => 'Token is invalid',
                ],
            ];
        }

        $accountModel = $this->module->modelStatic('Account');
        $accountModel = $accountModel::findOne($userToken->accountId);
        $accountModel->setScenario('reset');

        if (
            $accountModel->load(Yii::$app->request->post(), '')
            && $accountModel->save()
        ) {
            $userToken->markAsUsed();
            return;
        }

        $accountModel->validate();
        return $accountModel;
    }

    /**
     * @OA\Post(path="/accounts/login/",
     *    tags={"accounts"},
     *    summary="Login",
     *    description="",
     *    @OA\RequestBody(
     *        @OA\Schema(
     *            type="object",
     *            @OA\Property(
     *                property="email",
     *                type="string",
     *                description="Email address"
     *            ),
     *            @OA\Property(
     *                property="password",
     *                type="string",
     *                description="Password"
     *            ),
     *            @OA\Property(
     *                property="rememberMe",
     *                type="boolean",
     *                description="Remember Me"
     *            )
     *        )
     *    ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionLogin()
    {
        /**
         * @var \modules\account\models\forms\LoginForm $model
         * @var \modules\account\models\api\Account $account
         * @var \modules\account\models\Token $token
         */
        $account = $this->module->modelStatic('Account');
        $model = $this->module->model(
            'LoginForm',
            [
                'roles' => [Role::ROLE_CRM_ADMIN],
                'accountQuery' => $account::find(),
            ]
        );

        $model->load(Yii::$app->request->post(), '');
        if ($model->validate()) {
            $account = $model->getAccount();
            if (!$account->isCrmAdmin()) {
                $model->addError('password', 'Incorrect email or password');
                return $model;
            }
            Yii::$app->user->login($account);
            $expireTime = $this->module->loginExpireTime;
            if ($model->rememberMe) {
                $expireTime = $this->module->loginExpireTimeRemmember;
            }
            $expireTime = date("Y-m-d H:i:s", strtotime($expireTime));
            $token = $this->module->model('Token');
            $token = $token::generate($account->id, $token::TYPE_TOKEN, Yii::$app->request->userIP, $expireTime);
            $account->token = (string)$token->generateJwtToken();

            return $account;
        }

        return $model;
    }

    /**
     * @OA\Post(path="/accounts/invite-employee/",
     *     tags={"accounts"},
     *     summary="Invite employee",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *          @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     description="Email of new employee",
     *                       property="email",
     *                       type="string",
     *                 ),
     *                 @OA\Property(
     *                     description="Id of role",
     *                       property="roleId",
     *                       type="integer",
     *                 ),
     *                @OA\Property(
     *                     description="Id of a team",
     *                       property="teamId",
     *                       type="integer",
     *                 ),
     *          ),
     *     ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionInviteEmployee()
    {
        $form = new InviteEmployeeForm();
        $form->load(Yii::$app->request->post(), '');
        if ($form->validate()) {
            $form->sendInvitation();
        }
        return $form;
    }


    /**
     * @OA\Post(path="/accounts/signup-employee/",
     *     tags={"accounts"},
     *     summary="Sign up employee",
     *     description="",
     *     security={{"Bearer":{}}},
     *    @OA\RequestBody(
     *        @OA\Schema(
     *            type="object",
     *            @OA\Property(
     *                property="firstName",
     *                type="string",
     *                description="First Name"
     *            ),
     *            @OA\Property(
     *                property="lastName",
     *                type="string",
     *                description="Last Name"
     *            ),
     *            @OA\Property(
     *                property="token",
     *                type="string",
     *                description="Token"
     *            ),
     *            @OA\Property(
     *                property="newPassword",
     *                type="string",
     *                description="Password"
     *            ),
     *            @OA\Property(
     *                property="newPasswordConfirm",
     *                type="string",
     *                description="Repeat Password"
     *            ),
     *        )
     *    ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionSignupEmployee()
    {
        $form = new SignUpEmployeeForm();
        $form->load(Yii::$app->request->post(), '');
        if ($form->validate()) {
            return $form->signUp();
        }
        return $form;
    }



    /**
     * @OA\Get(path="/constants/",
     *     tags={"constants"},
     *     summary="Get constants",
     *     description="",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function actionConstants()
    {
        $result = [];
        $array = [
            'gender' => ConstantsHelper::gender(),
            'genderJob' => ConstantsHelper::genderJob(),
            //return old values of rate for support old tutors with rate bigger that 100
            'rateRange' => ['min' => 20, 'max' => 250],
            'schoolGradeLevel' => ConstantsHelper::schoolGradeLevel(),
            'clientLessonStatus' => ConstantsHelper::clientLessonStatus(),
            'clientPaymentStatus' => ConstantsHelper::clientPaymentStatus(),
            'clientSortList' => ProfileClientSearch::getSortList(),
            'startLessonTime' => ConstantsHelper::startLessonTime(),
            'lessonOccur' => ConstantsHelper::lessonOccur(),
            'transactionObjectType' => ConstantsHelper::transactionObjectType(),
            'transactionStatus' => ConstantsHelper::transactionStatus(),
            'transactionTypes' => ConstantsHelper::transactionTypes(),
            'distanceArray' => TutorSearch::getDistanceArray(),
            'grades' => Grade::byCategoriesList(),
        ];

        foreach ($array['grades'] as $k => $item) {
            $array['grades'][$k] = ConstantsHelper::convertArray($item);
        }

        foreach ($array as $key => $item) {
            $result[$key] = ConstantsHelper::convertArray($item);
        }

        $result['clientFlags'] = ConstantsHelper::clientFlags();
        $result['refundReasons'] = ConstantsHelper::getClientRefundsReasons();
        $result['rematchReasons'] = ConstantsHelper::getClientRematchReasons();
        $result['phoneNumberTypes'] = ConstantsHelper::phoneNumberTypes();
        $result['refundRematchStatisticStartDate'] = AccountReturn::STATISTIC_DATE_START;

        return $result;
    }

    /**
     * @OA\Get(path="/config/",
     *     tags={"config"},
     *     summary="Get config",
     *     description="",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function actionConfig()
    {
        /**
         * @var $chatModule \modules\chat\Module
         */
        $chatModule = Yii::$app->getModule('chat');

        return [
            'chat' => [
                'appId' => (int)$chatModule->application_id,
                'authKey' => $chatModule->auth_key,
                'authSecret' => $chatModule->secret_key,
            ],
            'job' => [
                'maxFileSize' => JobHelper::getMaxFileSize(),
                'fileExtensions' => JobHelper::getFileExtensions(),
                'fileMimeTypes' => JobHelper::getFileMimeTypes(),
                'maxFiles' => JobHelper::getMaxFiles(),
            ],
        ];
    }

    /**
     * @OA\Get(
     *     path="/payment/token/account/",
     *     tags={"payment"},
     *     summary="Get client data by payment token",
     *     description="",
     *     @OA\Parameter(
     *         description="Client Payment Token",
     *         in="query",
     *         name="token",
     *         required=true,
     *         type="string"
     *     ),
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *             property="companyLogoUrl",
     *             type="string",
     *             description="Company logo URL"
     *         ),
     *         @OA\Property(
     *             property="companyName",
     *             type="string",
     *             description="Company name"
     *         ),
     *         @OA\Property(
     *             property="firstName",
     *             type="string",
     *             description="First name"
     *         ),
     *         @OA\Property(
     *             property="accountId",
     *             type="string",
     *             description="Client ID"
     *         ),
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
    public function actionAccountByPaymentToken($token)
    {
        $token = Token::findByToken($token, Token::TYPE_CLIENT_PAYMENT);
        if (!$token) {
            throw new NotFoundHttpException();
        }

        /**
         * @var $account AccountClient
         */
        $account = AccountClient::findOneWithoutRestrictions($token->accountId);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        return [
            'companyLogoUrl' => '',
            'companyName' => '',
            'firstName' => $account->profile->firstName,
            'accountId' => $account->id,
        ];
    }


    /**
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Pusher\PusherException
     */
    public function actionPusherAuth()
    {
        $account = Yii::$app->user->identity;
        if (!$account->isCrmAdmin() && !$account->isCompanyEmployee()) {
            throw new BadRequestHttpException('You are not allow for this action');
        }

        $post = Yii::$app->request->post();
        if (!isset($post['channel_name'], $post['socket_id'])) {
            throw new InvalidArgumentException('Empty params');
        }
        return json_decode(Yii::$app->pusher->auth($post['channel_name'], $post['socket_id']));
    }
}
