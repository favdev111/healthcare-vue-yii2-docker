<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%health_profile_insurance}}`.
 */
class m201207_080655_create_health_profile_insurance_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%health_profile_insurance}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'insuranceCompanyId' => $this->integer()->null(),
            'groupNumber' => $this->string(50)->null(),
            'policyNumber' => $this->string(50)->null(),
            'locationZipCodeId' => $this->integer()->unsigned()->null(),
            'address' => $this->string()->null(),
            'googlePlaceId' => $this->string()->null(),
            'dateOfBirth' => $this->date()->null(),
            'firstName' => $this->string()->notNull(),
            'lastName' => $this->string()->notNull(),
            'socialSecurityNumber' => $this->string(50)->null(),
            'isPrimary' => $this->boolean()->notNull(),
            'healthProfileId' => $this->bigInteger()->unsigned()->notNull(),
        ]);

        $this->createIndex("unique-healthProfileId-isPrimary", $this->table, ['healthProfileId', 'isPrimary'], true);
        $this->addForeignKey('foreign-healthProfileId', $this->table, 'healthProfileId', 'health_profile', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('foreign-insuranceCompanyId', $this->table, 'insuranceCompanyId', 'insurance_company', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('foreign-locationZipCodeId', $this->table, 'locationZipCodeId', 'location_zipcode', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
