<?php

use yii\db\Migration;

/**
 * Class m201109_162315_lead_clear
 */
class m201109_162315_lead_clear extends Migration
{
    protected $tableName = '{{%leads}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->tableName, 'subject');
        $this->dropColumn($this->tableName, 'subjectId');
        $this->dropColumn($this->tableName, 'isCategory');
        $this->dropColumn($this->tableName, 'zipCode');
        $this->dropColumn($this->tableName, 'distance');
        $this->dropColumn($this->tableName, 'isSearchPage');
        $this->dropColumn($this->tableName, 'backendType');
        $this->dropColumn($this->tableName, 'siteType');

        $this->renameColumn($this->tableName, 'firstName', 'name');
        $this->renameColumn($this->tableName, 'phone', 'phoneNumber');
        $this->renameColumn($this->tableName, 'description', 'data');

        $this->alterColumn($this->tableName, 'data', $this->json());

        $this->addColumn($this->tableName, 'externalId', $this->string());
        $this->addColumn($this->tableName, 'ip', $this->string(39));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201109_162315_lead_clear cannot be reverted.\n";

        return false;
    }
}
