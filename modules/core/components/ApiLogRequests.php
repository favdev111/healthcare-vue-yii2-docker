<?php

namespace modules\core\components;

use modules\core\models\ApiLogRequest;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Class Settings
 */
class ApiLogRequests extends Component implements BootstrapInterface
{
    public const STATUS_ERROR = 'error';
    public const STATUS_STARTED = 'started';
    public const STATUS_SUCCESS = 'success';

    /**
     * @var bool Is logging enabled
     */
    public $enable = false;

    /** @var null|ApiLogRequest $logRequest */
    private $logRequest;

    /**
     * @param \yii\web\Application $app
     */
    public function bootstrap($app)
    {
        if (!$this->enable) {
            return;
        }

        $app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'processEventBeforeRequest']);
        $app->on(Application::EVENT_BEFORE_ACTION, [$this, 'processEventBeforeAction']);
        register_shutdown_function([$this, 'processEventAfterSend']);
    }

    public function processEventBeforeRequest()
    {
        $request = Yii::$app->request;
        $logRequest = new ApiLogRequest();
        $logRequest->request_method = $request->method;
        $logRequest->request_url = $request->absoluteUrl;
        $logRequest->status = static::STATUS_STARTED;
        $logRequest->request_body_params = $this->compressArrayValue(
            $request->isGet
                ? $request->getQueryParams()
                : $request->getBodyParams()
        );
        $logRequest->started_at = microtime(true);

        if ($logRequest->save(false)) {
            $this->logRequest = $logRequest;
        }
    }

    public function processEventBeforeAction()
    {
        $logRequest = $this->logRequest;
        $logRequest->controller_name = Yii::$app->controller->id;
        $logRequest->action_name = Yii::$app->controller->action->id;
        $logRequest->save(false);
    }

    public function processEventAfterSend()
    {
        if (!$this->logRequest) {
            return;
        }

        $response = Yii::$app->response;
        if (is_array($response->data)) {
            $content = Json::encode($response->data, JSON_PRETTY_PRINT);
        } elseif (is_string($response->data)) {
            $content = $response->data;
        } else {
            $content = $response->content;
        }

        $this->logRequest->status =
            $response->isSuccessful
                ? static::STATUS_SUCCESS
                : static::STATUS_ERROR;
        $this->logRequest->status_code = $response->statusCode;
        $this->logRequest->response = $this->compressValue($content);
        $this->logRequest->finished_at = microtime(true);

        $this->logRequest->save(false);
    }

    /**
     * Compress value
     *
     * @param $value
     *
     * @return string
     */
    private function compressArrayValue($value)
    {
        return gzdeflate(Json::encode($value, JSON_PRETTY_PRINT), 6);
    }

    /**
     * Compress value
     *
     * @param $value
     *
     * @return string
     */
    private function compressValue($value)
    {
        return gzdeflate($value, 6);
    }

    private function getNowDateTime(): string
    {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s.u');
    }
}
