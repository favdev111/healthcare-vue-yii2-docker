<?php

use yii\db\Migration;

/**
 * Class m200930_135950_add_profile_fields
 */
class m200930_135950_add_profile_fields extends Migration
{
    public $tableNameProfile = '{{%account_profile}}';
    public $tableNameLicence = '{{%account_licence_state}}';
    public $tableNameLicenceTelehealth = '{{%account_licence_state}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableNameProfile, 'insurance_company_text', $this->text()->null());
        $this->addColumn($this->tableNameLicence, 'licence', $this->string()->notNull());

        $this->alterColumn($this->tableNameLicence, 'accountId', $this->integer()->unsigned()->notNull());
        $this->alterColumn($this->tableNameLicence, 'stateId', $this->integer()->unsigned()->notNull());

        $this->alterColumn($this->tableNameLicenceTelehealth, 'accountId', $this->integer()->unsigned()->notNull());
        $this->alterColumn($this->tableNameLicenceTelehealth, 'stateId', $this->integer()->unsigned()->notNull());

        $this->addColumn($this->tableNameProfile, 'disciplinary_action_text', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
