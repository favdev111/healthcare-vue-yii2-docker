<?php

use yii\db\Migration;

/**
 * Class m201120_121231_account_update_rate_policy
 */
class m201120_121231_account_update_rate_policy extends Migration
{
    protected $tableName = 'account';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn($this->tableName, 'rate_policy', 'ratePolicy');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn($this->tableName, 'ratePolicy', 'rate_policy');
    }
}
