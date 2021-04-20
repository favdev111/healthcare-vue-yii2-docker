<?php

use yii\db\Migration;

/**
 * Class m201104_111434_remove_insurance_id_from_profile
 */
class m201104_111434_remove_insurance_id_from_profile extends Migration
{
    public $tableNameProfile = '{{%account_profile}}';
    public $tableNameAccountInsurance = '{{%account_insurance_company}}';
    public $column = 'insuranceCompanyId';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->tableNameProfile, $this->column);
        $this->createTable($this->tableNameAccountInsurance, [
            'id' => $this->primaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned(),
            'insuranceCompanyId' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
