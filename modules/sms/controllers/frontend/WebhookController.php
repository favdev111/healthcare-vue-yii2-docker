<?php

namespace modules\sms\controllers\frontend;

use Codeception\Util\HttpCode;
use common\helpers\EmailHelper;
use common\models\Lead;
use common\models\Sms;
use modules\account\models\Profile;
use modules\sms\models\FollowUp;
use Twilio\Twiml;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use Twilio\Security\RequestValidator;
use yii\web\Response;
use Twilio\TwiML\MessagingResponse;
use Yii;

class WebhookController extends Controller
{
    const COUNT_NUMBER_IN_PHONE_NUMBER = 10;
    protected $searchQuery = '';

    protected function validateWebhook()
    {
        try {
            $url = \Yii::$app->request->absoluteUrl;
            $post = \Yii::$app->request->post();
            $signature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'];
            $validator = new RequestValidator(\Yii::$app->twilio->token);
            return $validator->validate($signature, $url, $post);
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), 'incoming-sms');
        }
        return false;
    }

    /**
     * @param bool $followUp
     * @return array|\yii\db\ActiveRecord|null
     */
    protected function findSms(bool $followUp = false)
    {
        $fromNumber = \Yii::$app->request->post('From');
        $clearNumber = substr($fromNumber, -1 * static::COUNT_NUMBER_IN_PHONE_NUMBER);
        $query = Sms::find()
            ->byPhone($clearNumber)
            ->byStatus(Sms::STATUS_SENT_TO_TWILIO)
            ->andWhere(['userResponseStatus' => null])
            ->orderBy('id DESC')
            ->limit(1);
        if ($followUp) {
            $query->andWhere(['type' => Sms::$typesWithUserResponse]);
        }

        $this->searchQuery = (clone($query))->createCommand()->getRawSql();

        return $query->one();
    }

    public function beforeAction($action)
    {
        \Yii::info("\nProcess new incoming message...", 'incoming-sms');
        \Yii::info('POST :' . json_encode(\Yii::$app->request->post()), 'incoming-sms');
        if (!$this->validateWebhook()) {
            \Yii::error('Webhook validation failed.', 'incoming-sms');
            throw new ForbiddenHttpException();
        }
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIncomingMessage()
    {
        \Yii::$app->response->statusCode = HttpCode::NO_CONTENT;
        try {
            /**
             * @var \modules\sms\components\Sms $component
             */
            $component = \Yii::$app->sms;
            $messageText = \Yii::$app->request->post('Body');
            if (empty($messageText)) {
                \Yii::error('Receive answer for sms  with empty body. SID: ' . \Yii::$app->request->post('SmsSid'), 'incoming-sms');
                return false;
            }

            \Yii::info("\nLooking for sms...", 'incoming-sms');
            /**
             * @var Sms $sms
             */
            //looking for sms in db related to follow-up functionality
            $sms = $this->findSms(true);
            //if sms related to follow-up functionality
            if (!empty($sms) && $sms->isFollowUp()) {
                \Yii::info("Sms model with id {$sms->id} was found!", 'incoming-sms');
                $responseStatus = $component->processUserResponse($messageText);
                $component->saveUserResponse($sms, $messageText, $responseStatus);
                $answer = FollowUp::selectSmsAnswer($sms);
                \Yii::info("Sms model updated!", 'incoming-sms');
            } else {
                //looking for sms in db
                $sms = $this->findSms();
                //save response
                if (!empty($sms)) {
                    $sms->userResponseStatus = Sms::RESPONSE_STATUS_NOTHING;
                    $component->saveUserResponse($sms, $messageText, null);
                } else {
                    \Yii::info(
                        'Sms followup wasn\'t found in database phone '
                        . \Yii::$app->request->post('From')
                        . " SID: " . \Yii::$app->request->post('SmsSid')
                        . " Sql query: {$this->searchQuery}",
                        'incoming-sms'
                    );
                }
                $this->notFollowUpHandler(Yii::$app->request->post());
                $answer = Sms::RESPONSE_DEFAULT;
            }

            //send response sms
            if (!empty($answer)) {
                \Yii::$app->response->format = Response::FORMAT_XML;
                \Yii::$app->response->statusCode = HttpCode::OK;
                $responseForReply = new Twiml();
                $responseForReply->message('')->body($answer);
                \Yii::info('Webhook response body: ' . (string)($responseForReply), 'incoming-sms');
                \Yii::info("Sms response: $answer", 'incoming-sms');
                Yii::$app->response->content = (string)($responseForReply);
            }
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage() . ' ' . $exception->getTraceAsString(), 'incoming-sms');
        }
    }

    public function notFollowUpHandler(array $data)
    {
        $phone = $data['From'] ?? null;
        if (empty($phone)) {
            return;
        }

        $phoneUsa = substr($phone, -1 * static::COUNT_NUMBER_IN_PHONE_NUMBER);
        $result = [
            'phone' => $phone,
            'body' => $data['Body'] ?? '',
        ];
        /**
         * @var Lead $leadModel
         */
        $leadModel = Lead::find()->andWhere(['phone' => $phoneUsa])->limit(1)->orderBy(['id' => \SORT_DESC])->one();
        if ($leadModel) {
            $result['type'] = 'Lead';
            $result['name'] = $leadModel->firstName;
            $result['email'] = $leadModel->email;
            $result['subject'] = $leadModel->subject;
            $result['description'] = $leadModel->description;
        } elseif ($accountProfileModel = Profile::find()->andWhere(['phoneNumber' => $phoneUsa])->orderBy(['id' => \SORT_DESC])->limit(1)->one()) {
            /**
             * @var Profile $accountProfileModel
             */
            $result['type'] = 'Account';
            $result['name'] = $accountProfileModel->showName;
            $result['email'] = $accountProfileModel->account->email;
            $result['accountId'] = $accountProfileModel->accountId;
        }
    }

    public function actionStatusCallback()
    {
        \Yii::$app->response->statusCode = HttpCode::NO_CONTENT;
        $sid = \Yii::$app->request->post('SmsSid');
        /**
         * @var Sms $sms
         */
        $sms = Sms::find()->bySid($sid)->one();
        if (empty($sms)) {
            \Yii::error('STATUS UPDATE ERROR: sms with sid ' . $sid . ' mot found.', 'sms');
            return false;
        }

        try {
            /**
             * @var \modules\sms\components\Sms $component
             */
            $component = \Yii::$app->sms;
            $component->updateDeliveryStatus($sms, Sms::getDeliveryStatus(\Yii::$app->request->post('SmsStatus')));
            return true;
        } catch (\Throwable $exception) {
            \Yii::error('STATUS UPDATE ERROR: ' . $exception->getMessage() . ' ' . $exception->getTraceAsString(), 'incoming-sms');
            return false;
        }
    }
}
