<?php

use yii\db\Migration;

/**
 * Class m201014_115942_create_health_tests_tables
 */
class m201014_115942_create_health_tests_tables extends Migration
{
    public $healthTests = [
        'Food Sensitivity Test',
        'Micronutrient Test',
        'Heavy Metals Test',
        'Additives Test',
        'Neural Zoomer Plus',
        'Neurotransmitter Test',
        'Fungal Antibodies Test',
        'Hormones Test',
        'Gut Zoomer',
        'Mycotoxins Test',
        'Environmental Toxins Test',
        'CardiaX Test',
        'Tickborne Disease Test',
        'IBSSure Test',
    ];
    public $healthTest = '{{%health_test}}';
    public $healthTestCategory = '{{%health_test_category}}';
    public $healthTestToCategory = '{{%health_test_to_category}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->healthTest, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
            'createdAt' => $this->dateTime(),
            'updatedAt' => $this->dateTime()->null(),
        ]);

        $this->createTable($this->healthTestCategory, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
            'createdAt' => $this->dateTime(),
            'updatedAt' => $this->dateTime()->null(),
        ]);

        $this->createTable($this->healthTestToCategory, [
            'id' => $this->primaryKey()->unsigned(),
            'categoryId' => $this->integer()->unsigned(),
            'healthTestId' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk_htToCat_category',
            $this->healthTestToCategory,
            'categoryId',
            $this->healthTestCategory,
            'id'
        );

        $this->addForeignKey(
            'fk_htToCat_health',
            $this->healthTestToCategory,
            'healthTestId',
            $this->healthTest,
            'id'
        );

        foreach ($this->healthTests as $healthTest) {
            $test = new \common\models\health\HealthTest();
            $test->name = $healthTest;
            $test->save(false);

            $category = new \common\models\health\HealthTestCategory();
            $category->name = $healthTest;
            $category->save(false);

            $healthToCategory = new \common\models\health\HealthTestToCategory();
            $healthToCategory->healthTestId = $test->id;
            $healthToCategory->categoryId = $category->id;
            $healthToCategory->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->healthTestToCategory);
        $this->dropTable($this->healthTestCategory);
        $this->dropTable($this->healthTest);
    }
}
