<?php

use yii\db\Migration;

/**
 * Class m201020_064609_rename_columns
 */
class m201020_064609_rename_columns extends Migration
{
    public $tableName = '{{%account_profile}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn($this->tableName, 'disciplinary_action_text', 'disciplinaryActionText');
        $this->renameColumn($this->tableName, 'insurance_company_text', 'insuranceCompanyText');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
