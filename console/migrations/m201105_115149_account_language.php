<?php

use yii\db\Migration;

/**
 * Class m201105_115149_account_language
 */
class m201105_115149_account_language extends Migration
{
    protected $tableName = 'account_language';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned(),
            'languageId' => $this->integer(),
            'createdAt' => $this->timestamp(),
        ]);

        $this->addForeignKey(
            'fk_account',
            $this->tableName,
            'accountId',
            'account',
            'id'
        );

        $this->addForeignKey(
            'fk_language',
            $this->tableName,
            'languageId',
            'language',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_account', $this->tableName);
        $this->dropForeignKey('fk_language', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
