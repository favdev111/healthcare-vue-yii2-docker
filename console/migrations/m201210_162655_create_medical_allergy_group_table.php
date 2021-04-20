<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Handles the creation of table `{{%medical_allergy_group}}`.
 */
class m201210_162655_create_medical_allergy_group_table extends Migration
{
    /**
     * @var string
     */
    private $table = '{{%medical_allergy_group}}';
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
            'allergyCategoryId' => $this->primaryKey()->unsigned()
        ]);

        $this->addForeignKey('fk_allergyCategoryId', $this->table, 'allergyCategoryId', 'allergy_category', 'id', 'CASCADE', 'CASCADE');

        // Insert data to table
        $this->addDataToTable();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function addDataToTable()
    {
        $medicalGroupCategories = $this->getData();
        $medicalGroupCategories = array_unique($medicalGroupCategories);
        $ids = (new Query())
            ->select(['id'])
            ->from('allergy_category')
            ->where(['name' => $medicalGroupCategories])
            ->all();

        $this->db
            ->createCommand()
            ->batchInsert($this->table, ['allergyCategoryId'], $ids)
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
        $items = Json::decode($fileContent);
        $items = array_filter($items, static function ($value) {
            return ArrayHelper::getValue($value, 'isMedicalGroup', false);
        });
        return array_keys($items);
    }
}
