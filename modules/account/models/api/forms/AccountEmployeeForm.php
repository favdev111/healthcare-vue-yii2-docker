<?php

namespace modules\account\models\api\forms;

use common\components\behaviors\ChildModelErrorsBehavior;
use modules\account\models\Account;
use modules\account\models\AccountWithBlocked;
use modules\account\models\api\AccountEmployee;
use modules\account\models\Role;
use yii\base\Model;

/**
 * Class AccountEmployeeForm
 * @property Account $account
 * @package modules\account\models\api\forms
 */
class AccountEmployeeForm extends Model
{
    protected $notEmptyAttributes;
    public $account;
    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $status;
    public $phoneNumber;

    public function rules()
    {
        return [
            [['lastName', 'firstName', 'email', 'phoneNumber'], 'string'],
            [['status'], 'integer'],
            [['account'], 'required'],
        ];
    }

    public function behaviors()
    {
        return [
            'ChildModelErrorsBehavior' => ChildModelErrorsBehavior::class,
        ];
    }


    public function update()
    {
        if (!$this->validate()) {
            return $this;
        }

        $account = $this->account;
        $profile = $account->profile ?? null;

        if (!empty($profile)) {
            $profile->setAttributes($this->getFormAttributes(), false);
            $profile->setScenario($profile::SCENARIO_ADMIN_EDIT_EMPLOYEE);
            if ($profile->validate()) {
                if (!$profile->save(false)) {
                    $this->addError('account', 'Failed to save data');
                };
            } else {
                $this->collectErrors($profile);
                return $this;
            }
        }

        $account->setAttributes($this->getFormAttributes(), false);

        if ($account->validate()) {
            if (!$account->save(false)) {
                $this->addError('account', 'Failed to save data');
            };
        } else {
            $this->collectErrors($account);
            return $this;
        }

        $account->refresh();
        return $account;
    }

    public function getFormAttributes()
    {
        if (empty($this->notEmptyAttributes)) {
            $attrs = [];
            foreach ($this->getAttributes() as $name => $attribute) {
                if (!empty($attribute) && !is_null($attribute)) {
                    $attrs[$name] = $attribute;
                }
            }
            $this->notEmptyAttributes = $attrs;
        }
        return $this->notEmptyAttributes;
    }

    public static function getForbiddenMessage()
    {

        return 'You are not allowed to perform this action. Please contact ';
    }
}
