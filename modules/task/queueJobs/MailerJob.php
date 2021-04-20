<?php

namespace modules\task\queueJobs;

use modules\task\components\RetryableJob;
use yii\helpers\ArrayHelper;
use yii\mail\MessageInterface;

/**
 * Class MailerJob
 *
 * @package console\components
 */
class MailerJob extends RetryableJob
{
    /**
     * @var string|array $email Email
     */
    public $email;

    /**
     * @var string $subject Subject
     */
    public $subject;

    /**
     * @var string HTML content of message
     */
    public $contentHtml;

    /**
     * @var string TEXT content of message
     */
    public $contentText;

    /**
     * @vat null|string $bccEmail Send blind carbon copy to this email
     */
    public $bccEmail;

    /**
     * @var string $content HTML content of message
     */
    public $config = [];

    /**
     * @inheritDoc
     */
    public function execute($queue): bool
    {
        if (empty($this->email)) {
            throw new \Exception('Email is empty.');
        }

        $result = $this->createMessage($this->config)
            ->setHtmlBody($this->contentHtml)
            ->setTextBody($this->contentText)
            ->setTo($this->email)
            ->setSubject($this->subject);

        if (!empty($this->bccEmail)) {
            $result->setBcc($this->bccEmail);
        }

        $sendResult = $result->send();

        if (!$sendResult) {
            $this->logError('Not sent block :' . json_encode($result));
            throw new \Swift_SwiftException('Send email error.');
        }

        return true;
    }

    /**
     * Creates a new message instance.
     * The newly created instance will be initialized with the configuration specified by [[messageConfig]].
     * If the configuration does not specify a 'class', the [[messageClass]] will be used as the class
     * of the new message instance.
     * @var array $config
     * @return MessageInterface message instance.
     */
    protected function createMessage($config = [])
    {
        $mailer = \Yii::$app->mailer;
        $config = ArrayHelper::merge(
            $mailer->messageConfig,
            $config
        );

        if (!array_key_exists('class', $config)) {
            $config['class'] = $mailer->messageClass;
        }
        $config['mailer'] = $mailer;
        return \Yii::createObject($config);
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error): bool
    {

        $this->logError(
            'Retry function :'
            . get_class($error) . "\n"
            . $error->getMessage() . "\n"
            . $error->getTraceAsString()
        );
        return false;
    }

    protected function logError($message)
    {
        \Yii::error($this->email . "\n" . $message, 'mail-error');
    }
}
