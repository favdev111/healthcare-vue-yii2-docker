<?php

use yii\db\Migration;

/**
 * Class m201120_111433_account_add_rate_policy
 */
class m201120_111433_account_add_rate_policy extends Migration
{
    protected $tableName = 'account';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'rate_policy', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'rate_policy');
    }
}
