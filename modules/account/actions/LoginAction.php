<?php

namespace modules\account\actions;

use modules\account\models\AccountAccessToken;
use modules\account\models\api2\Account;
use modules\account\responses\AccountResponse;
use Yii;
use yii\base\Action;

class LoginAction extends Action
{
    public $roles;
    public $accountQuery;

    public function run()
    {
        /**
         * @var \modules\account\models\forms\LoginForm $model
         * @var \modules\account\Module $module
         * @var \modules\account\models\Account $account
         */
        $platformId = $this->getPlatform();
        $deviceToken = $this->getDeviceToken();

        $request = Yii::$app->request;
        $module = $this->controller->module;
        $model = $module->model(
            'LoginForm',
            [
                'roles' => (array) $this->roles,
                'accountQuery' => $this->accountQuery,
            ]
        );
        $model->load($request->post(), '');
        if (!$model->validate()) {
            return $model;
        }

        $account = $model->getAccount();
        /**
         * @var $accessToken AccountAccessToken
         */
        $accessToken = $account->getAccessTokens()->andWhere(['deviceToken' => $deviceToken])->limit(1)->one();
        if (!$accessToken) {
            $accessToken = $module->model('AccountAccessToken');
            $accessToken->accountId = $account->id;
            $accessToken->platform = $platformId;
            $accessToken->deviceToken = $deviceToken;
        }

        $accessToken->generateNewToken();
        $accessToken->save(false);

        Yii::$app->user->login($account);

        return [
            'accessToken' => $accessToken->token,
        ];
    }

    protected function responseWithToken(Account $account, $accessToken)
    {
        $account->accessToken = $accessToken;
        $account->scenario = Account::SCENARIO_LOGIN;
        return $account;
    }

    protected function generateAccessToken(Account $account)
    {
        /**
         * @var $accessToken AccountAccessToken
         */
        $accessToken = $this->controller->module->model('AccountAccessToken');
        $accessToken->platform = $this->getPlatform();
        $accessToken->deviceToken = $this->getDeviceToken();
        $accessToken->generateNewToken();
        $account->link('accessTokens', $accessToken);

        return $accessToken;
    }

    /**
     * @return string
     */
    protected function getPlatform()
    {
        return Yii::$app->request->getPlatform();
    }

    /**
     * @return string
     */
    protected function getDeviceToken()
    {
        return Yii::$app->request->getDeviceToken();
    }
}
