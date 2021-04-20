<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification}}`.
 */
class m201130_153557_create_notification_table extends Migration
{
    private $table = '{{%notification}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey(),
            'level' => $this->string(),
            'notifiable_type' => $this->string(),
            'notifiable_id' => $this->integer()->unsigned(),
            'subject' => $this->string(),
            'body' => $this->text(),
            'read_at' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
            'data' => $this->text()
        ]);
        $this->createIndex('notifiable', $this->table, ['notifiable_type', 'notifiable_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
