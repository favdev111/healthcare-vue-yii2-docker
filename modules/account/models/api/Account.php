<?php

namespace modules\account\models\api;

use api\components\rbac\Rbac;

/**
 * @inheritdoc
 */
class Account extends \modules\account\models\Account
{
    public $token;

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = [
            'id',
            'email',
            'avatarUrl',
            'profile',
            'roleId',
            'publicId',
            'verified' => function () {
                return \modules\payment\models\CardInfo::find()->byCustomerActiveCard($this->id)->exists();
            },
            'teamId' => function () {
                return !\Yii::$app->user->isGuest ? ($this->accountTeam->teamId ?? null) : null;
            },
        ];

        if ($this->token) {
            array_unshift($fields, 'token');
        }

        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['paymentAccountId'] = function () {
            $paymentAccount = $this->paymentAccount;
            // TODO: Refactoring required. Returns company's data in case of company admin
            return $paymentAccount->id ?? null;
        };
        $extraFields['paymentCustomerId'] = function () {
            $paymentCustomer = $this->paymentCustomer;
            // TODO: Refactoring required. Returns company's data in case of company admin
            return $paymentCustomer->id ?? null;
        };
        // TODO: Will not work for company admin
        $extraFields['paymentAccount'] = 'paymentAccount';
        // TODO: Will not work for company admin
        $extraFields['paymentCustomer'] = 'paymentCustomer';
        $extraFields['isCanManageUsers'] = function () {
            return \Yii::$app->user->can(Rbac::PERMISSION_CAN_MANAGE_EMPLOYEES);
        };
        $extraFields['paymentVerified'] = function () {
            return true;
        };
        $extraFields['isStripeVerified'] = function () {
            return true;
        };
        $extraFields['commission'] = function () {
            return $this->commission;
        };
        $extraFields['clientStatistic'] = 'clientStatistic';
        return $extraFields;
    }
}
