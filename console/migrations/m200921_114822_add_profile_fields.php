<?php

use yii\db\Migration;

/**
 * Class m200921_114822_add_profile_fields
 */
class m200921_114822_add_profile_fields extends Migration
{
    public $tableName = 'account_profile';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'professional_type_id', $this->integer()->unsigned()->null());
        $this->addColumn($this->tableName, 'doctor_type_id', $this->integer()->unsigned()->null());
        $this->addColumn($this->tableName, 'npi_number', $this->string()->null());
        $this->addColumn($this->tableName, 'years_of_experience', $this->smallInteger()->unsigned()->null());
        $this->addColumn($this->tableName, 'is_board_certified', $this->smallInteger()->unsigned()->null());
        $this->addColumn($this->tableName, 'licence_number', $this->string()->null());
        $this->addColumn($this->tableName, 'has_disciplinary_action', $this->smallInteger()->unsigned()->null());
        $this->addColumn($this->tableName, 'currently_enrolled', $this->smallInteger()->unsigned()->null());
        $this->addColumn($this->tableName, 'insurance_company_id', $this->integer()->unsigned()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'professional_type_id');
        $this->dropColumn($this->tableName, 'doctor_type_id');
        $this->dropColumn($this->tableName, 'npi_number');
        $this->dropColumn($this->tableName, 'years_of_experience');
        $this->dropColumn($this->tableName, 'is_board_certified');
        $this->dropColumn($this->tableName, 'licence_number');
        $this->dropColumn($this->tableName, 'has_disciplinary_action');
        $this->dropColumn($this->tableName, 'currently_enrolled');
        $this->dropColumn($this->tableName, 'insurance_company_id');
    }
}
