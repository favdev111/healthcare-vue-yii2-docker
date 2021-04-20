<?php

use yii\db\Migration;

/**
 * Class m201113_054253_adjust_insurance_company_table
 */
class m201113_054253_adjust_insurance_company_table extends Migration
{
    public $tableName = '{{%insurance_company}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'createdAt', $this->dateTime());
        $this->addColumn($this->tableName, 'updatedAt', $this->dateTime());
        $this->addColumn($this->tableName, 'createdBy', $this->integer()->unsigned());
        $this->addColumn($this->tableName, 'updatedBy', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'createdAt');
        $this->dropColumn($this->tableName, 'updatedAt');
        $this->dropColumn($this->tableName, 'createdBy');
        $this->dropColumn($this->tableName, 'updatedBy');
    }
}
