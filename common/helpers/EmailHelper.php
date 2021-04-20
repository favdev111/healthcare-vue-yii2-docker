<?php

namespace common\helpers;

use modules\task\queueJobs\MailerJob;
use Yii;
use yii\helpers\ArrayHelper;
use yii\swiftmailer\Mailer;

/**
 * Class EmailHelper
 * @package common\helpers
 */
class EmailHelper
{
    /**
     * @param string $email
     * @param string $subject
     * @param string|array $template
     * @param array $data
     * @param array $configMailer
     * @param array $configMessage
     * @param null|string $bccEmail Send blind carbon copy to this email
     * @return mixed|string|void|null return ID of the job
     */
    public static function send(
        string $email,
        string $subject,
        $template,
        array $data,
        array $configMailer = [],
        array $configMessage = [],
        $bccEmail = null
    ) {
        if (env('MAIL_SEND_QUEUE')) {
            return (bool)self::sendEmailQueue(
                $email,
                $subject,
                $template,
                $data,
                $configMailer,
                $configMessage,
                $bccEmail
            );
        }

        return (bool)self::sendEmail(
            $email,
            $subject,
            $template,
            $data,
            $configMailer,
            $configMessage
        );
    }

    /**
     * @param string $email
     * @param string $subject
     * @param string|array $template
     * @param array $data
     * @param array $configMessage
     * @param array $configMailer
     * @return mixed|string|void|null return ID of the job
     */
    protected static function sendEmail(
        string $email,
        string $subject,
        $template,
        array $data,
        array $configMailer = [],
        array $configMessage = []
    ) {
        /** @var Mailer $mailer */
        $mailer = clone Yii::$app->mailer;

        // modify view path to module views
        $viewPath = ArrayHelper::getValue($configMailer, 'viewPath');
        if ($viewPath) {
            $mailer->viewPath = $viewPath;
        }

        // modify html layout param
        $htmlLayout = ArrayHelper::getValue($configMailer, 'htmlLayout', true);
        if (!$htmlLayout) {
            $mailer->htmlLayout = false;
        }

        try {
            // send email
            $message = $mailer->compose($template, $data);
            if (!empty($configMessage)) {
                Yii::configure($message, $configMessage);
            }

            $result = $message
                ->setTo($email)
                ->setSubject($subject)
                ->send();
        } catch (\Exception $e) {
            Yii::error(
                'Failed to send email to ' . $email . ' with subject ' . $subject . ' Error: ' . $e->getMessage(),
                'mail'
            );
            $result = false;
        }

        return $result;
    }

    protected static function sendEmailQueue(
        string $email,
        string $subject,
        $template,
        array $data,
        array $configMailer = [],
        array $configMessage = [],
        $bccEmail = null
    ) {
        /** @var Mailer $mailer */
        $mailer = clone \Yii::$app->mailer;

        if (!empty($configMailer)) {
            Yii::configure($mailer, $configMailer);
        }

        if (is_array($template)) {
            if (isset($view['html'])) {
                $html = $mailer->render($template['html'], $data, $mailer->htmlLayout);
            }
            if (isset($view['text'])) {
                $text = $mailer->render($template['text'], $data, $mailer->textLayout);
            }
        } else {
            $html = $mailer->render($template, $data, $mailer->htmlLayout);
        }

        if (empty($html)) {
            return false;
        }

        return \Yii::$app->yiiQueue->push(new MailerJob([
            'contentHtml' => $html,
            'contentText' => $text ?? null,
            'email' => $email,
            'subject' => $subject,
            'config' => $configMessage,
            'bccEmail' => $bccEmail
        ]));
    }
}
