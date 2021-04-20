<?php

namespace modules\account\models\query;

use common\components\ActiveQuery;
use common\helpers\AccountStatusHelper;
use modules\account\models\Account;
use modules\account\models\Lesson;
use modules\account\models\Role;
use Yii;

/**
 * Class AccountQuery
 * @package modules\account\models
 * @property string $tableName
 */
class AccountQuery extends ActiveQuery
{
    /**
     * By active status
     */
    public function byActiveStatus()
    {
        return $this->andWhere([$this->tableName . '.status' => AccountStatusHelper::STATUS_ACTIVE]);
    }

    /**
     * @return $this
     */
    public function tutor()
    {
        return $this->andWhere([$this->tableName . '.RoleId' => Role::ROLE_SPECIALIST]);
    }

    public function byId(int $id): self
    {
        return $this->andWhere([$this->tableName . '.id' => $id]);
    }

    /**
     * @return $this
     */
    public function tutorOrCompany()
    {
        return $this->andWhere([
            'or',
            [$this->tableName . '.roleId' => Role::ROLE_SPECIALIST],
            [$this->tableName . '.roleId' => Role::ROLE_CRM_ADMIN],
        ]);
    }

    /**
     * @return $this
     */
    public function student()
    {
        return $this->andWhere([$this->tableName . '.RoleId' => Role::ROLE_PATIENT]);
    }

    /**
     * @return $this
     */
    public function active()
    {
        return $this->andNonSuspended();
    }

    /**
     * @return $this
     */
    public function newRegistered()
    {
        return $this->andFilterCompare($this->tableName . '.createdAt', date('Y-m-d'), '>=');
    }

    public function isPatient()
    {
        return $this->andOnCondition(['roleId' => Role::ROLE_PATIENT]);
    }

    public function isSpecialist()
    {
        return $this->andOnCondition(['roleId' => Role::ROLE_SPECIALIST]);
    }

    public function isCrmAdmin()
    {
        return $this->andOnCondition(['roleId' => Role::ROLE_CRM_ADMIN]);
    }

    public function withConfirmedEmail()
    {
        return $this->andWhere([
            Account::tableName() . '.isEmailConfirmed' => true,
        ]);
    }

    public function andNonSuspended()
    {
        return $this->andWhere([
            'not',
            [
                Account::tableName() . '.status' => Account::SUSPENDED_STATUSES,
            ],
        ]);
    }

    public function clientInvited()
    {
        return $this->andWhere(['clientInvited' => true]);
    }

    public function byEmail($email)
    {
        return $this->andWhere(['email' => $email]);
    }

    public function isBatchPayments()
    {
        return $this->andWhere(['paymentProcessType' => Account::PAYMENT_TYPE_BATCH_PAYMENT]);
    }

    public function notEmployee()
    {
        return $this->andWhere(['not', [$this->tableName . '.roleId' => Role::ROLE_COMPANY_EMPLOYEE]]);
    }

    public function employee()
    {
        return $this->andWhere([$this->tableName . '.roleId' => Role::ROLE_COMPANY_EMPLOYEE]);
    }

    public function crmAdmin()
    {
        return $this->andWhere([$this->tableName . '.roleId' => Role::ROLE_CRM_ADMIN]);
    }

    public function ownEmployees()
    {
        return $this->employee();
    }


    public function companyMembers()
    {
        return $this->andWhere([$this->tableName . '.roleId' => Role::ROLE_COMPANY_EMPLOYEE]);
    }

    public function notDeleted()
    {
        return $this->andWhere(['not', [Account::tableName() . '.status' => AccountStatusHelper::STATUS_DELETED]]);
    }

    public function excludeHiddenProfiles()
    {
        return $this->andWhere(['hideProfile' => false]);
    }

    public function excludeHiddenOnMarketplace()
    {
        return $this->andWhere(['searchHide' => false]);
    }

    public function hasLessonWithinDays(int $days): self
    {
        $this->joinWith([
            'studentLessons' => function ($query) use ($days) {
                /**
                 * @var $query LessonQuery
                 */
                $dateTime = new \DateTime();
                $query->andWhere(['>=', Lesson::tableName() . '.createdAt', $dateTime->sub(new \DateInterval("P${days}D"))->format(Yii::$app->formatter->MYSQL_DATE)]);
            },
        ]);

        return $this;
    }
}
