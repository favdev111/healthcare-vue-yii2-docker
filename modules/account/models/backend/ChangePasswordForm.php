<?php

namespace modules\account\models\backend;

use modules\account\models\Account;
use common\components\validators\StrengthValidator;
use yii\base\ErrorException;
use yii\base\Model;

/**
 * Class ChangePasswordForm
 * @package modules\account\models\backend
 */
class ChangePasswordForm extends Model
{
    /**
     * @var string
     */
    public $newPassword;
    /**
     * @var string
     */
    public $newPasswordRepeat;
    /**
     * @var Account
     */
    protected Account $account;

    /**
     * ChangePasswordForm constructor.
     * @param Account $account
     * @param array $config
     */
    public function __construct(Account $account, $config = [])
    {
        $this->account = $account;
        parent::__construct($config);
    }

    /**
     * @return \string[][]
     */
    public function rules()
    {
        return [
            [['newPassword', 'newPasswordRepeat'], 'required'],
            ['newPassword', StrengthValidator::class, 'usernameValue' => 'password'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword', 'message' => "Passwords don't match"]
        ];
    }

    /**
     * @return Account|null
     * @throws ErrorException
     */
    public function change(): ?Account
    {
        if (!$this->validate()) {
            return null;
        }

        $this->account->newPassword = $this->newPassword;

        if (!$this->account->save()) {
            throw new ErrorException('Account was not saved');
        }

        $this->resetToDefault();

        return $this->account;
    }

    public function resetToDefault()
    {
        $this->newPassword = null;
        $this->newPasswordRepeat = null;
    }
}
