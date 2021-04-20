<?php

namespace modules\core\models;

use common\components\ActiveRecord;

/**
 * This is the model class for table "api_log_request".
 *
 * @property integer $id
 * @property string $request_method
 * @property resource $request_url
 * @property resource $request_body_params
 * @property resource $controller_name
 * @property resource $action_name
 * @property resource $response
 * @property string $status
 * @property int $status_code
 * @property float $started_at
 * @property float $finished_at
 */
class ApiLogRequest extends ActiveRecord
{
    public static $actionsList = [];

    /** @inheritdoc */
    public static function tableName(): string
    {
        return '{{%api_log_request}}';
    }

    /** @inheritDoc */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'request_type' => 'Request Type',
            'request_url' => 'Request Url',
            'request_body_params' => 'Body Params',
            'response' => 'Response',
            'controller_name' => 'Controller',
            'action_name' => 'Action',
            'status' => 'Status',
            'started_at' => 'Started At',
            'finished_at' => 'Finished At',
        ];
    }

    public function getRequestDuration(): ?float
    {
        if ($this->started_at && $this->finished_at) {
            return ($this->finished_at - $this->started_at);
        }

        return null;
    }

    /**
     * @param string|null $actionName
     * @return string
     */
    public static function getRequests(string $actionName = null): string
    {
        return static::$actionsList[$actionName] ?? 'N/A';
    }
}
