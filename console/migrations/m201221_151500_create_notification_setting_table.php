<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification_setting}}`.
 */
class m201221_151500_create_notification_setting_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%notification_setting}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'accountId' => $this->integer()->unsigned()->notNull(),
            'notificationTypeId' => $this->tinyInteger()->notNull()
        ]);

        $this->createIndex('unique-notification_setting-accountId-notificationTypeId', $this->table, ['accountId', 'notificationTypeId'], true);
        $this->addForeignKey('foreign-notification_setting-account-accountId', $this->table, 'accountId', 'account', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
