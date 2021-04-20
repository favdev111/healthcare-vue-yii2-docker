<?php

namespace modules\sms\components;

use common\components\Formatter;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class Sms extends Component
{
    public $twilioNumber;

    /**
     * @param \common\models\Sms $model
     * @param bool $runValidation
     * @return bool
     */
    protected function saveModel(\common\models\Sms $model, bool $runValidation = true): bool
    {
        if (!$model->save($runValidation)) {
            static::logError("Failed to save sms with id = {$model->id}.");
            return false;
        }
        return true;
    }

    /**
     * @param string $message
     */
    public static function logError(string $message): void
    {
        \Yii::error($message, 'sms');
    }

    public function init()
    {
        parent::init();
        if (empty($this->twilioNumber)) {
            throw new InvalidConfigException('Twilio number hasn\'t been set.');
        }
    }

    /**
     * Creates row in sms table. Returns new SMS message model. Parameters should be validated outside.
     * Throws exception i case of validation error.
     * @param string $message
     * @param int $accountId
     * @param string $phone
     * @param int $type
     * @param array $extraData
     * @return \common\models\Sms
     * @throws Exception
     */
    public function createSms(string $message, string $phone, int $type, int $accountId = null, array $extraData = []): \common\models\Sms
    {
        $sms = new \common\models\Sms([
            'text' => $message,
            'accountId' => $accountId,
            'phone' => $phone,
            'status' => \common\models\Sms::STATUS_WAITING_TO_SEND,
            'type' => $type,
            'extraData' => $extraData,
        ]);
        if (!$sms->save()) {
            $reason = $sms->hasErrors() ? json_encode($sms->getErrors()) : 'Unknown error';
            throw new Exception('Failed to create sms:' . $reason);
        }
        return $sms;
    }

    /**
     * Sending message from "sms" table to Twilio
     * @param \common\models\Sms $sms
     * @return bool
     */
    public function sendSms(\common\models\Sms $sms): bool
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        try {
            $twillio = \Yii::$app->twilio->getClient();
            $sentSms = $twillio->messages->create(
                $sms->phone,
                array(
                    "body" => $sms->text,
                    "from" => $this->twilioNumber,
                    "statusCallback" => \Yii::$app->urlManager->createAbsoluteUrl(['/sms/webhook/status-callback']),
                )
            );
        } catch (\Throwable $exception) {
            $errorMessage = "Failed to send sms with id = {$sms->id}. Reason {$exception->getMessage()} \n Trace: {$exception->getTraceAsString()}";

            $sms->extraData = array_merge($sms->extraData, ['errorMessageWhileSending' => $errorMessage]);
            $sms->save(false);

            static::logError($errorMessage);
            return false;
        }

        $sms->sentAt = date($formatter->MYSQL_DATETIME);
        $sms->twilioMessageSID = $sentSms->sid;
        $sms->status = \common\models\Sms::STATUS_SENT_TO_TWILIO;

        return $this->updateDeliveryStatus($sms, \common\models\Sms::getDeliveryStatus($sentSms->status));
    }

    /**
     * Using for tracking sms delivery status in webhhook handler
     * @param \common\models\Sms $sms
     * @param int $newStatus
     * @return bool
     */
    public function updateDeliveryStatus(\common\models\Sms $sms, int $newStatus): bool
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        $sms->deliveryStatus = $newStatus;
        $sms->deliveryStatusUpdatedAt = date($formatter->MYSQL_DATETIME);
        return $this->saveModel($sms, false);
    }

    /**
     * Using in webhook handler for saving followup responses
     * @param \common\models\Sms $sms
     * @param string $responseText
     * @param int $userResponseStatus
     * @return bool
     */
    public function saveUserResponse(\common\models\Sms $sms, string $responseText, int $userResponseStatus = null): bool
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        $sms->userResponse = $responseText;
        $sms->userResponseStatus = $userResponseStatus ?? null;
        $sms->userResponseAt = date($formatter->MYSQL_DATETIME);
        return $this->saveModel($sms, false);
    }

    /**
     * Get response status (YES or NO)
     * @param string $userResponse
     * @return int
     */
    public function processUserResponse(string $userResponse): int
    {
        $lowerResponse = strtolower($userResponse);
        $yesPosition = strpos($lowerResponse, 'yes');
        $noPosition = strpos($lowerResponse, 'no');

        if ($yesPosition !== false && $noPosition !== false) {
            //if yes was first - it's the answer, otherwise - no is the answer.
            return $yesPosition < $noPosition ? \common\models\Sms::RESPONSE_STATUS_YES : \common\models\Sms::RESPONSE_STATUS_NO;
        } elseif ($yesPosition !== false) {
            return \common\models\Sms::RESPONSE_STATUS_YES;
        } elseif ($noPosition !== false) {
            return \common\models\Sms::RESPONSE_STATUS_NO;
        } else {
            return \common\models\Sms::RESPONSE_STATUS_NOTHING;
        }
    }
}
