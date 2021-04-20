<?php

namespace common\components\app;

/**
 * @property-read \modules\account\Module $moduleAccount
 * @property-read \modules\core\Module $moduleCore
 * @property-read \modules\chat\Module $moduleChat
 * @property-read \modules\payment\Module $modulePayment
 */
trait ModuleAppTrait
{
    // -------------------------------- Modules definitions --------------------------------

    /*
     * Use names like `getModule` and module name
     * Add only common module, used in whole application and defined in common config
     */

    /**
     * @return \modules\account\Module
     */
    public function getModuleAccount()
    {
        return $this->getModule('account');
    }

    /**
     * @return \modules\core\Module
     */
    public function getModuleCore()
    {
        return $this->getModule('core');
    }

    /**
     * @return \modules\chat\Module
     */
    public function getModuleChat()
    {
        return $this->getModule('chat');
    }

    /**
     * @return \modules\payment\Module
     */
    public function getModulePayment()
    {
        return $this->getModule('payment');
    }

    /**
     * @return \modules\sms\Module
     */
    public function getModuleSms()
    {
        return $this->getModule('sms');
    }

    /**
     * @return \modules\notification\Module
     */
    public function getModuleNotification()
    {
        return $this->getModule('notification');
    }

    // -------------------------------- END Modules definitions --------------------------------
}
