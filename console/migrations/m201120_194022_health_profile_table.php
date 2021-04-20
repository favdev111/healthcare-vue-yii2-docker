<?php

use yii\db\Migration;

/**
 * Class m201120_194022_health_profile_table
 */
class m201120_194022_health_profile_table extends Migration
{
    protected $tableName = 'health_profile';
    protected $tableNameAccount = 'account';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned()->notNull(),
            'firstName' => $this->string(),
            'lastName' => $this->string(),
            'phoneNumber' => $this->string(12),
            'email' => $this->string(),
            'birthday' => $this->date(),
            'gender' => $this->char(1),
            'height' => $this->decimal(7, 3),
            'weight' => $this->decimal(7, 3),
            'zipcode' => $this->string(20),
            'address' => $this->string(),
            'country' => $this->char(2),
            'googlePlaceId' => $this->string(),
            'latitude' => $this->decimal(10, 7),
            'longitude' => $this->decimal(10, 7),
            'maritalStatusId' => $this->tinyInteger()->unsigned(),
            'childrenCount' => $this->tinyInteger()->notNull()->defaultValue(0),
            'educationLevelId' => $this->tinyInteger()->unsigned(),
            'occupation' => $this->text(),
            'employer' => $this->text(),
            'smoke' => $this->tinyInteger()->unsigned(),
            'drink' => $this->tinyInteger()->unsigned(),
            'otherSubstances' => $this->text(),
            'createdAt' => $this->timestamp()->null()->defaultValue(null),
            'updatedAt' => $this->timestamp()->null()->defaultValue(null),
        ]);

        $this->addForeignKey('fk-health_profile--account', $this->tableName, 'accountId', $this->tableNameAccount, 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
