<?php

use yii\db\Migration;

/**
 * Class m201019_153053_rename_fields
 */
class m201019_153053_rename_fields extends Migration
{
    public $tableName = '{{%account_profile}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn($this->tableName, 'professional_type_id', 'professionalTypeId');
        $this->renameColumn($this->tableName, 'doctor_type_id', 'doctorTypeId');
        $this->renameColumn($this->tableName, 'npi_number', 'npiNumber');
        $this->renameColumn($this->tableName, 'years_of_experience', 'yearsOfExperience');
        $this->renameColumn($this->tableName, 'is_board_certified', 'isBoardCertified');
        $this->renameColumn($this->tableName, 'licence_number', 'licenceNumber');
        $this->renameColumn($this->tableName, 'has_disciplinary_action', 'hasDisciplinaryAction');
        $this->renameColumn($this->tableName, 'currently_enrolled', 'currentlyEnrolled');
        $this->renameColumn($this->tableName, 'insurance_company_id', 'insuranceCompanyId');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
