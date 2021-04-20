<?php

namespace common\components\behaviors;

use common\events\TutorLowBalanceEvent;
use yii\base\InvalidCallException;
use yii\base\Application;

/**
 * Class ToasterNotificationBehavior
 * @package common\components\behaviors
 */
class ToasterNotificationBehavior extends ApplicationBehavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            TutorLowBalanceEvent::NAME => 'tutorLowBalance',
        ];
    }

    /**
     * trigger when tutors balance is to low
     *
     * @param TutorLowBalanceEvent $event
     */
    public function tutorLowBalance(TutorLowBalanceEvent $event)
    {
//        $this->owner->session->addFlash('error', "Your payment balance is to low");
    }
}
