<?php

use yii\db\Migration;
use yii\helpers\Json;

/**
 * Handles the creation of table `{{%lifestyle_diet}}`.
 */
class m201211_085525_create_lifestyle_diet_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%lifestyle_diet}}';
    /**
     * @var string
     */
    private const ALIAS_TO_FILE = '@console/migrations/data/lifestyleDiets';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
            'createdAt' => $this->dateTime()->defaultExpression('NOW()'),
            'updatedAt' => $this->dateTime()->defaultExpression('NOW()'),
        ]);

        // insert data to table
        $this->addDataToTable();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function addDataToTable()
    {
        $lifestyleDiets = $this->getData();

        $lifestyleDiets = array_unique($lifestyleDiets);
        $lifestyleDiets = array_map(static function ($value) {            // add array to each value item
            return [$value];
        }, $lifestyleDiets);

        $this->db
            ->createCommand()
            ->batchInsert($this->table, ['name'], $lifestyleDiets)
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
