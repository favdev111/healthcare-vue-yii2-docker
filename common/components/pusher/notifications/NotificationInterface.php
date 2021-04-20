<?php

namespace common\components\pusher\notifications;

/**
 * Interface NotificationInterface
 */
interface NotificationInterface
{
    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return string
     */
    public function getMessage(): string;
}
