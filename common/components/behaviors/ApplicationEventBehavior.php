<?php

namespace common\components\behaviors;

use common\events\TutorLowBalanceEvent;
use yii\base\Application;
use yii\base\Event;

/**
 * Class ApplicationEventBehavior
 * @package common\components\behaviors
 */
class ApplicationEventBehavior extends ApplicationBehavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Application::EVENT_BEFORE_ACTION => 'beforeActionHandler',
        ];
    }

    /**
     * @param Event $event
     */
    public function beforeActionHandler(Event $event)
    {
        if ($this->isTutorWithLowBalance()) {
            $this->owner->trigger(TutorLowBalanceEvent::NAME, new TutorLowBalanceEvent());
        }
    }

    /**
     * @return bool
     */
    private function isTutorWithLowBalance()
    {
        if ($this->owner->user->isGuest) {
            return false;
        }

        $identity = $this->owner->user->identity;

        return ($identity->isTutor() && !$identity->isTransferAvailable());
    }
}
