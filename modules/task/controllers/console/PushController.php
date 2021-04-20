<?php

namespace modules\task\controllers\console;

use modules\account\models\AccountAccessToken;
use UrbanIndo\Yii2\Queue\Worker\Controller;
use Yii;

class PushController extends Controller
{
    /** @var \aksafan\fcm\source\components\Fcm $fcm */
    protected $fcm;

    public function beforeAction($action)
    {
        $this->fcm = Yii::$app->fcm;
        return parent::beforeAction($action);
    }

    public function actionSend(
        array $accountIds,
        string $key,
        string $message,
        array $data
    ) {
        foreach ($accountIds as $accountId) {
            $tokens = AccountAccessToken::generateTokensForPush($accountId);
            if (empty($tokens)) {
                return;
            }

            $data['key'] = strtoupper($key);

            if (empty($tokens)) {
                return;
            }

            // Keys and values must be strings
            $_data = array_map('strval', $data);

            $tokensToRemove = [];
            foreach ($tokens as $token) {
                try {
                    /** @var \aksafan\fcm\source\responses\apiV1\TokenResponse $result */
                    $result = $this->fcm
                        ->createRequest()
                        ->setTarget(\aksafan\fcm\source\builders\apiV1\MessageOptionsBuilder::TOKEN, $token)
                        ->setData($_data)
                        ->setNotification(Yii::$app->name, $message)
                        ->setAndroidConfig([
                            'notification' => [
                                'sound' => 'default',
                            ],
                        ])
                        ->setApnsConfig([
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                ],
                            ],
                        ])
                        ->send();

                    if ($result->getError()) {
                        Yii::error(
                            "[$token]" . '[' . $result->getErrorStatus() . '] '
                            . $result->getErrorMessage()
                            . "\n"
                            . var_export($result->getErrorDetails(), true),
                            'push'
                        );
                    }
                } catch (\Exception $exception) {
                    Yii::error(
                        "[$token] "
                        . $exception->getMessage()
                        . "\n"
                        . $exception->getTraceAsString(),
                        'push'
                    );
                }

                if (isset($result) && !empty($result->getTokensToDelete())) {
                    $tokensToRemove = array_merge($tokensToRemove, $result->getTokensToDelete());
                }
            }

            $tokensToRemove = array_unique($tokensToRemove);
            if ($tokensToRemove) {
                $this->removeAccountAccessTokens($tokensToRemove);
            }
        }
    }

    protected function removeAccountAccessTokens(array $tokens)
    {
        AccountAccessToken::deleteAll([
            'and',
            ['in', 'deviceToken', $tokens],
        ]);
    }
}
