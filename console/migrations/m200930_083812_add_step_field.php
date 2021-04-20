<?php

use yii\db\Migration;

/**
 * Class m200930_083812_add_step_field
 */
class m200930_083812_add_step_field extends Migration
{
    public $columnName = 'registrationStep';
    public $tableName = 'account';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, $this->columnName, $this->smallInteger()->unsigned()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }
}
