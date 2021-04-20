<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%licence_state}}`.
 */
class m200922_112605_create_licence_state_table extends Migration
{
    public $tableNames = ['account_telehealth_state', 'account_licence_state'];
    public $stateTable = 'state';
    public $accountTable = 'account';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->tableNames as $tableName) {
            $this->createTable($tableName, [
                'id' => $this->primaryKey(),
                'accountId' => $this->integer()->unsigned(),
                'stateId' => $this->integer()->unsigned(),
            ]);

            $this->addForeignKey($tableName . '_accountId_account', $tableName, 'accountId', $this->accountTable, 'id');
            $this->addForeignKey($tableName . '_stateId_account', $tableName, 'stateId', $this->stateTable, 'id');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->tableNames as $tableName) {
            $this->dropTable($tableName);
        }
    }
}
