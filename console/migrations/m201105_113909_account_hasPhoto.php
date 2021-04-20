<?php

use yii\db\Migration;

/**
 * Class m201105_113909_account_hasPhoto
 */
class m201105_113909_account_hasPhoto extends Migration
{
    protected $tableName = 'account';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'hasPhoto', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'hasPhoto');
    }
}
