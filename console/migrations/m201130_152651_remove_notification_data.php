<?php

use yii\db\Migration;

/**
 * Class m201130_152651_remove_notification_data
 */
class m201130_152651_remove_notification_data extends Migration
{
    /**
     * @var string[]
     */
    protected $dropTables = [
        'notification_account',
        'notification_setting',
        'notification',
    ];

    protected $dropTablesColumns = [
        'account' => [
            'countSendNotification',
            'countSendCardErrorNotification',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->dropTables as $tableName) {
            if ($this->db->schema->getTableSchema($tableName)) {
                $this->dropTable($tableName);
            }
        }

        foreach ($this->dropTablesColumns as $table => $columns) {
            foreach ($columns as $column) {
                $schema = $this->db->schema->getTableSchema($table);
                if ($schema && $schema->getColumn($column)) {
                    $this->dropColumn($table, $column);
                }
            }
        }
    }
}
