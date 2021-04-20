<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%health_profile_health_medical}}`.
 */
class m201211_120712_create_health_profile_health_medical_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%health_profile_health_medical}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'healthProfileId' => $this->bigInteger()->unsigned()->notNull(),
            'text' => $this->text()->null(),
            'internalId' => $this->bigInteger()->null(),
            'medicalTypeId' => $this->tinyInteger()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey('foreign-health_profile_health_medical-healthProfileId', $this->table, 'healthProfileId', 'health_profile', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
