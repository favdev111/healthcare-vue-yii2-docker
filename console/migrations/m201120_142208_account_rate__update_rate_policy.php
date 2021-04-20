<?php

use yii\db\Migration;

/**
 * Class m201120_142208_account_rate__update_rate_policy
 */
class m201120_142208_account_rate__update_rate_policy extends Migration
{
    protected $tableName = 'account_rate';
    protected $tableNameAccount = 'account';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->tableNameAccount, 'ratePolicy');
        $this->addColumn($this->tableName, 'ratePolicy', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->addColumn($this->tableNameAccount, 'ratePolicy', $this->boolean()->defaultValue(false));
        $this->dropColumn($this->tableName, 'ratePolicy');
    }
}
