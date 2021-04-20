<?php

namespace modules\account\models\forms;

use common\helpers\AccountStatusHelper;
use modules\account\helpers\SignupHelper;
use modules\account\models\Account;
use modules\account\models\AccountSocialAuth;
use modules\account\models\query\AccountQuery;
use yii\authclient\OAuthToken;
use yii\base\Model;
use Yii;
use yii\httpclient\Response;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string Password
     */
    public $password;

    public $socialAccessToken;
    public $socialId;
    public $socialUserId;

    /**
     * @var bool If true, users will be logged in for $loginDuration
     */
    public $rememberMe = false;

    /**
     * @var \modules\account\models\Account
     */
    protected $account = false;

    /**
     * @var \modules\account\Module
     */
    public $module;

    protected $roles = [];

    /**
     * @var AccountQuery
     */
    protected $accountQuery;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required', 'when' => function () {
                return !$this->socialAccessToken
                    && !$this->socialId
                    && !$this->socialUserId
                ;
            }
            ],
            ['email', 'email'],
            ['password', 'validatePassword'],
            ['rememberMe', 'boolean'],
            [['socialAccessToken', 'socialId', 'socialUserId'], 'string'],
        ];
    }


    //company client must have an invitation for login
    public function isHasInvitationValidator()
    {
        if (!empty($this->account) && Yii::$app->user->isPatient() && !$this->account->isClientInvited()) {
            $this->addError('email', 'This account doesn\'t have an invitation');
        }
    }

    public function afterValidate()
    {
        if (!$this->hasErrors()) {
            $this->validateUser();
        }

        parent::afterValidate();
    }

    /**
     * Validate user
     */
    public function validateUser()
    {
        if ($this->socialAccessToken && $this->socialId && $this->socialUserId) {
            $account = $this->checkSocial();
            if (!$account) {
                $this->addError('socialId', 'Sign-in using ' . ucfirst($this->socialId) . ' auth failed.');
                return;
            }

            $this->account = $account;
        } else {
            // check for valid user or if user registered using social auth
            $account = $this->getAccount();
            if (!$account) {
                $this->addError('email', 'Incorrect email or password');
                return;
            }
        }

        // check if user is banned
        if ($account && $account->isSuspended()) {
            if ($account->banReason) {
                $this->addError('email', "Your account has been blocked - " . $account->banReason);
                return;
            }
            switch ($account->status) {
                case AccountStatusHelper::STATUS_BLOCKED:
                    $this->addError('', 'Your account has been blocked in violation of Heytutor\'s policies. To reinstate your account, please either email : info@winitclinic.com or call ' . Yii::$app->phoneNumber->getPhoneNumberFormatted() . '.');
                    return;
                default:
                    // Delete and Block statuses
                    $this->addError('', 'Your account has been blocked. To reinstate your account, please either email : info@winitclinic.com or call ' . Yii::$app->phoneNumber->getPhoneNumberFormatted() . '.');
                    return;
            }
        }
    }

    /**
     * Validate password
     */
    public function validatePassword()
    {
        // skip if there are already errors
        if ($this->hasErrors()) {
            return;
        }
        /** @var \modules\account\models\Account $account */
        // check if password is correct
        $account = $this->getAccount();
        if (!$account || !$account->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect email or password');
        }
    }

    /**
     * Get account based on email
     * @return \modules\account\models\Account|null
     */
    public function getAccount()
    {
        if ($this->account === false) {
            $this->account = $this->accountQuery
                ->andWhere(['email' => $this->email])
                ->andWhere(['roleId' => (array)$this->roles])
                ->limit(1)
                ->one();
        }
        return $this->account;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
            'rememberMe' => 'Remember Me',
        ];
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function setAccountQuery(AccountQuery $query)
    {
        $this->accountQuery = $query;
    }

    protected function checkSocial(): ?Account
    {
        $socialData = SignupHelper::getSocialData($this->socialId, $this->socialAccessToken);
        if (!$socialData) {
            return null;
        }

        if ((string)$socialData['id'] !== $this->socialUserId) {
            return null;
        }

        $model = AccountSocialAuth::find()->where([
            'source' => $this->socialId,
            'sourceId' => $this->socialUserId,
        ])->one();

        if (!$model) {
            return null;
        }

        return $model->getAccount()
            ->andWhere(['roleId' => (array)$this->roles])
            ->limit(1)
            ->one();
    }
}
