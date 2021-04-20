<?php

use yii\db\Migration;

/**
 * Class m201002_110646_adjust_account_reward
 */
class m201002_110646_adjust_account_reward extends Migration
{
    public $tableName = '{{%account_reward}}';
    public $columnName = 'certificationOrg';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn($this->tableName, $this->columnName, $this->text()->null());
        $this->renameColumn($this->tableName, 'yearRecieved', 'yearReceived');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
