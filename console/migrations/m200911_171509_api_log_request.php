<?php

use yii\db\Migration;

/**
 * Class m200911_171509_api_log_request
 */
class m200911_171509_api_log_request extends Migration
{
    protected $tableName = '{{%api_log_request}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'request_method' => $this->string(),
            'request_url' => $this->binary(),
            'request_body_params' => $this->binary(),
            'response' => $this->binary(),
            'controller_name' => $this->string(),
            'action_name' => $this->string(),
            'status' => $this->string(),
            'status_code' => $this->smallInteger()->unsigned(),
            'started_at' => $this->decimal(14,4)->unsigned(),
            'finished_at' => $this->decimal(14,4)->unsigned(),
            'KEY `request_method` (`request_method`)',
            'KEY `created_at` (`started_at`)',
            'KEY `controller_name` (`controller_name`)',
            'KEY `action_name` (`action_name`)',
            'KEY `status` (`status`)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
