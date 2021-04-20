<?php

namespace modules\account\models\forms;

use common\components\behaviors\ChildModelErrorsBehavior;
use modules\account\models\Account;
use modules\account\models\AccountTeam;
use modules\account\models\Role;
use modules\account\models\Team;
use Yii;
use yii\base\Model;

/**
 * Class InviteEmployeeFrom
 * @package modules\account\models\forms
 * @property Account $account
 */
class InviteEmployeeForm extends Model
{
    public $email;
    public $successfullySent = false;
    public $accountCreated = false;
    public $roleId;
    public $accountId;
    public $teamId;
    protected $relatedAccount;

    public function behaviors()
    {
        return [
          'ChildModelErrorsBehavior' => ChildModelErrorsBehavior::class,
        ];
    }

    public function rules()
    {
        return [
          [['email'], 'required'],
          [['email'], 'email'],
          [['email'], function () {
              //if user already registered (has profile data)
            if (!empty($this->account) && !empty($this->account->profile)) {
                if ($this->account->isActive()) {
                    $this->addError('email', 'This email has already been taken');
                } elseif ($this->account->isStatusBlocked()) {
                    $this->addError('email', 'This account has been blocked');
                }
            }
          }
          ],
          [['teamId'], 'integer'],
          [['teamId'], 'in', 'range' => array_keys(Team::getList())],
          [['roleId'], 'integer'],
          [['roleId'], 'default', 'value' => Role::ROLE_COMPANY_EMPLOYEE],
          [
              ['teamId'],
              'required',
              'when' => function () {
                    return $this->roleId === Role::ROLE_COMPANY_EMPLOYEE;
              }
          ],
          [['roleId'], 'in', 'range' => [Role::ROLE_COMPANY_EMPLOYEE]],
          [['roleId'], function () {
            if ($this->roleId == Role::ROLE_CRM_ADMIN) {
                $this->addError('roleId', 'Admin can invite only employees');
            }
          }
          ],
        ];
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getAccount()
    {
        if (empty($this->relatedAccount)) {
            $this->relatedAccount = Account::findWithoutRestrictions()->notDeleted()
                ->byEmail($this->email)
                ->limit(1)
                ->one();
        }
        return $this->relatedAccount;
    }

    public function sendInvitation()
    {
        //looking for account
        $account =  $this->account;
        //create new one
        if (empty($account)) {
            $account = new Account([
                'email' => $this->email,
                'roleId' => $this->roleId,
                'status' => Account::STATUS_ACTIVE,
            ]);
            if (!$account->save()) {
                //method from ChildModelErrorsBehavior
                $this->collectErrors($account);
                return false;
            }

            if ($account->isCompanyEmployee()) {
                $accountTeamRelation = new AccountTeam();
                $accountTeamRelation->teamId = $this->teamId;
                $accountTeamRelation->accountId = $account->id;
                $accountTeamRelation->save(false);
            }

            $this->accountCreated = true;
        }

        $this->accountId = $account->id;

        if ($account->sendEmailEmployeeInvitation(\Yii::$app->user->identity)) {
            $this->successfullySent = true;
            return true;
        }

        return false;
    }
}
