<?php

namespace modules\account\components;

use common\components\role\RoleMethodServiceInterface;
use common\helpers\Url;
use common\components\ZipCodeHelper;
use modules\account\models\AccountAvailability;
use modules\account\Module;
use Yii;

/**
 * User component
 *
 * @property \modules\account\models\Account|\yii\web\IdentityInterface|null $identity The identity object associated with the currently logged-in user. null is returned if the user is not logged in (not authenticated).
 * @property-read boolean $isShowAvailabilityPopUp
 */
class Account extends \yii\web\User
{
    /**z
     * @inheritdoc
     */
    public $identityClass = 'modules\account\models\Account';

    public function init()
    {
        parent::init();

        $timeZone = ZipCodeHelper::getTimeZoneByUserIP();
        if ($timeZone) {
            Yii::$app->formatter->timeZone = $timeZone;
        }
    }

    /**
     * @inheritdoc
     */
    public function getIsGuest()
    {
        /** @var \modules\account\models\Account $account */

        // check if user is banned. if so, log user out and redirect home
        // https://github.com/amnah/yii2-user/issues/99
        $account = $this->getIdentity();
        if ($account && ! $account->isActive()) {
            $this->logout();
            Yii::$app->getResponse()->redirect(['/'])->send();
        }

        return $account === null;
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public function getIsLoggedIn()
    {
        return ! $this->getIsGuest();
    }

    /**
     * Get user's display name
     * @param string $default
     * @return string
     */
    public function getDisplayName($default = "")
    {
        /** @var \modules\account\models\Account $account */
        $account = $this->getIdentity();

        return $account ? $account->displayName : $default;
    }

    /**
     * Check if user can do $permissionName.
     * If "authManager" component is set, this will simply use the default functionality.
     * Otherwise, it will use our custom permission system
     * @param string $permissionName
     * @param array $params
     * @param bool $allowCaching
     * @return bool
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        // check for auth manager to call parent
        $auth = Yii::$app->getAuthManager();
        if ($auth) {
            return parent::can($permissionName, $params, $allowCaching);
        }

        // otherwise use our own custom permission (via the role table)
        /** @var \modules\account\models\Account $account */
        $account = $this->getIdentity();

        return $account ? $account->can($permissionName) : false;
    }

    public function getIdentityById($accountId)
    {
        $class = $this->identityClass;
        $identity = $class::findIdentity($accountId);

        return $identity ?: false;
    }

    public function isActive()
    {
        /** @var \modules\account\models\Account $account */
        $account = $this->getIdentity();

        return $account ? $account->status === \modules\account\models\Account::STATUS_ACTIVE && $account->isEmailConfirmed() : false;
    }

    /**
     * @param $excludeHold boolean Whether to exclude hold accounts or not
     * @return bool is current account suspicious (or hold)
     * @see Chat -> isSuspicious
     */
    public function isSuspicious($excludeHold = false)
    {
        /**
         * @var $account \modules\account\models\Account
         */
        if (Yii::$app->user->isGuest) {
            return Module::isIpSuspicious(Yii::$app->request->userIP);
        }
        $account = $this->getIdentity();

        return $account && $account->chat && $account->chat->isSuspicious($excludeHold);
    }

    public function isPatient()
    {
        return ! empty($this->identity) && $this->identity->isPatient();
    }

    /**
     * @return bool
     */
    public function getIsShowAvailabilityPopUp()
    {
        //if there is not availability data and pop-up wasn't shown before
        $availability = $this->identity->availability;
        if (empty($availability)) {
            return true;
        }

        return ! (bool)$availability->popUpShown;
    }

    public function markAvailabilityPopUpAsShown()
    {
        $availability = $this->identity->availability;
        if (empty($availability)) {
            $availability = new AccountAvailability();
            $availability->accountId = $this->identity->id;
        }
        $availability->popUpShown = true;
        $availability->save(false);
    }
}
