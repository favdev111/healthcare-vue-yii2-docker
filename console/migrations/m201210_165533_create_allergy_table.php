<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Handles the creation of table `{{%allergy}}`.
 */
class m201210_165533_create_allergy_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%allergy}}';
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
            'allergyCategoryId' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string()->notNull(),
            'createdAt' => $this->dateTime()->defaultExpression('NOW()'),
            'updatedAt' => $this->dateTime()->defaultExpression('NOW()'),
        ]);

        $this->createIndex('unique-allergy-table-allergyCategoryId-name', $this->table, ['allergyCategoryId', 'name'], true);
        $this->addForeignKey('fk-allergy-table-allergyCategoryId', $this->table, 'allergyCategoryId', 'allergy_category', 'id', 'CASCADE', 'CASCADE');

        // Insert data to table
        $this->addDataToTable();
    }

    /**
     * @throws \yii\db\Exception
     */
    private function addDataToTable()
    {
        $allergy = $this->getData();

        $values = [];
        foreach ($allergy as $category => $value) {

            $valueCategory = (new Query())
                ->select(['id'])
                ->from('allergy_category')
                ->where(['name' => $category])
                ->one();

            if (!$valueCategory) {
                continue;
            }

            $items = ArrayHelper::getValue($value, 'items', []);
            unset($item);
            foreach ($items as &$item) {
                $item = mb_strtolower($item);
                $item = ucfirst($item);
            }
            unset($item);

            $items = array_unique($items);
            foreach ($items as $item) {
                $values[] = [$valueCategory['id'], $item];
            }
        }

        $this->db
            ->createCommand()
            ->batchInsert($this->table, ['allergyCategoryId', 'name'], $values)
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
     * @throws Exception
     */
    protected function getData(): array
    {
        $pathToFile = Yii::getAlias(self::ALIAS_TO_FILE);
        $fileContent = file_get_contents($pathToFile);
        return Json::decode($fileContent);
    }
}
