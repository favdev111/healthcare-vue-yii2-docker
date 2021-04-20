<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%health_profile_health}}`.
 */
class m201211_114116_create_health_profile_health_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%health_profile_health}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'healthProfileId' => $this->bigInteger()->unsigned()->notNull(),
            'smokeId' => $this->tinyInteger()->null(),
            'drinkId' => $this->tinyInteger()->null(),
            'isOtherSubstance' => $this->boolean()->null(),
            'otherSubstanceText' => $this->text()->null(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addPrimaryKey('', $this->table, 'healthProfileId');
        $this->addForeignKey('foreign-health_profile_health-healthProfileId', $this->table, 'healthProfileId', 'health_profile', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
