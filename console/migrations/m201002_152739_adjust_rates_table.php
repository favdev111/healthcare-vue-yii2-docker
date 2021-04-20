<?php

use yii\db\Migration;

/**
 * Class m201002_152739_adjust_rates_table
 */
class m201002_152739_adjust_rates_table extends Migration
{
    public $tableName = '{{%account_rate}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'rate15', $this->double()->null());
        $this->addColumn($this->tableName, 'rate30', $this->double()->null());
        $this->addColumn($this->tableName, 'rate45', $this->double()->null());
        $this->addColumn($this->tableName, 'rate60', $this->double()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
