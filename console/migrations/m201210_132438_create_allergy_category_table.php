<?php

use yii\db\Migration;
use yii\helpers\Json;

/**
 * Handles the creation of table `{{%allergy_category}}`.
 */
class m201210_132438_create_allergy_category_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%allergy_category}}';
    /**
     * @var string
     */
    private const ALIAS_TO_FILE = '@console/migrations/data/allergy';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->unique()->notNull(),
        ]);

        // Insert categories to table
        $this->addDataToTable();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function addDataToTable()
    {
        $categories = $this->getData();
        $categories = array_unique($categories);
        $categories = array_map(static function ($value) {            // add array to each value item
            return [$value];
        }, $categories);

        $this->db
            ->createCommand()
            ->batchInsert($this->table, ['name'], $categories)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }

    /**
     * @return array
     */
    protected function getData(): array
    {
        $pathToFile = Yii::getAlias(self::ALIAS_TO_FILE);
        $fileContent = file_get_contents($pathToFile);
        $items = Json::decode($fileContent);
        return array_keys($items);
    }
}
