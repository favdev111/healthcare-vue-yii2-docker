<?php

namespace modules\account\models\forms;

use common\components\behaviors\ChildModelErrorsBehavior;
use modules\account\models\Account;
use modules\account\models\Profile;
use modules\account\Module;
use yii\base\Model;

class SignUpEmployeeForm extends Model
{
    public $firstName;
    public $token;
    public $lastName;
    public $newPassword;
    public $newPasswordConfirm;
    public $accountProfileModel;

    protected $accountModel;

    protected $userToken;

    public function rules()
    {
        return [
            [['token'], 'tokenValidator'],
            [['firstName', 'lastName', 'newPassword', 'token','newPasswordConfirm'], 'required'],
            [['token'], 'accountValidator', 'skipOnEmpty' => false, 'skipOnError' => true]
        ];
    }

    /**
     * find account model and validate it
     */
    public function accountValidator()
    {
        /**
         * @var Module $module
         */
        $module = \Yii::$app->getModule('account');
        /**
         * @var Account $accountModel
         */
        $accountModel = $module->modelStatic('Account');
        $accountModel = $accountModel::findOne($this->userToken->accountId);
        $accountModel->isEmailConfirmed = true;

        if (!$accountModel) {
            $this->addError('token', 'Token is invalid');
        }

        $accountModel->setScenario($accountModel::SCENARIO_REGISTER);
        $this->loadAndValidate($accountModel);

        if (empty($accountModel->profile)) {
            $profile = new Profile();
        } else {
            $profile = $accountModel->profile;
        }
        $profile->accountId = $accountModel->id;
        $profile->setScenario($profile::SCENARIO_ADMIN_EDIT_EMPLOYEE);
        $this->loadAndValidate($profile);
        $this->accountModel = $accountModel;
        $this->accountProfileModel = $profile;
    }

    public function behaviors()
    {
        return [
            'ChildModelErrorsBehavior' => ChildModelErrorsBehavior::class,
        ];
    }

    public function tokenValidator()
    {
        /** @var \modules\account\models\Account $accountModel */
        /** @var \modules\account\models\Token $userToken */
        $userToken = \Yii::$app->getModule('account')->modelStatic('Token');
        $this->userToken = $userToken::findByToken($this->token, $userToken::TYPE_PASSWORD_RESET);
        if (empty($this->userToken)) {
            $this->addError('token', 'Token is invalid');
        }
    }

    public function signUp()
    {
        /**
         * @var Account $accountModel
         */
        $accountModel = $this->accountModel;
        /**
         * @var Profile $profile
         */
        $profile = $this->accountProfileModel;
        /**
         * @var Module $module
         */
        $module = \Yii::$app->getModule('account');

        if (
            !$accountModel->hasErrors()
            && !$profile->hasErrors()
            && $accountModel->save(false)
            && $profile->save(false)
        ) {
            $accountModel->refresh();
            $this->userToken->markAsUsed();

            $expireTime = date("Y-m-d H:i:s", strtotime($module->loginExpireTime));
            $token = $module->modelStatic('Token');
            $token = $token::generate($accountModel->id, $token::TYPE_TOKEN, null, $expireTime);
            $accountModel->token = (string) $token->generateJwtToken();

            return $accountModel;
        }
        return false;
    }

    protected function loadAndValidate($model)
    {
        $model->load($this->getAttributes(), '');
        $model->validate();
        if ($model->hasErrors()) {
            $this->collectErrors($model);
        }
    }
}
