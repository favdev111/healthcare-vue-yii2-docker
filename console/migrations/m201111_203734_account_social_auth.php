<?php

use yii\db\Migration;

/**
 * Class m201111_203734_account_social_auth
 */
class m201111_203734_account_social_auth extends Migration
{
    protected $tableName = 'account_social_auth';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'accountId' => $this->integer()->unsigned()->notNull(),
            'source' => $this->string()->notNull(),
            'sourceId' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-account',
            $this->tableName,
            'accountId',
            'account',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
