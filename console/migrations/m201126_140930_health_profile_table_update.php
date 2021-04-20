<?php

use yii\db\Migration;

/**
 * Class m201126_140930_health_profile_table_update
 */
class m201126_140930_health_profile_table_update extends Migration
{
    protected $tableName = 'health_profile';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'isMain', $this->tinyInteger()->unsigned());
        $this->addColumn($this->tableName, 'relationshipId', $this->tinyInteger()->unsigned());
        $this->addColumn($this->tableName, 'deletedAt', $this->timestamp()->defaultValue(null));

        $this->createIndex('idx--isMain-accountId', $this->tableName, ['isMain', 'accountId']);
        $this->createIndex('idx--accountId-deletedAt', $this->tableName, ['accountId', 'deletedAt']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'isMain');
        $this->dropColumn($this->tableName, 'relationshipId');
        $this->dropColumn($this->tableName, 'deletedAt');
    }
}
