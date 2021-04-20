<?php

use common\models\health\AutoimmuneDisease as AutoimmuneDisease;
use common\models\health\HealthGoal as HealthGoal;
use common\models\health\Symptom as Symptom;
use yii\db\Migration;

/**
 * Class m201005_090159_import_symptoms_data
 */
class m201005_090159_import_symptoms_data extends Migration
{
    public $tableNames = [
        'symptom',
        'medical_condition',
        'autoimmune_disease',
        'health_goal',
    ];

    protected function saveModel(string $name, string $class)
    {
        $model = new $class();
        $model->name = ucfirst($name);

        if (!$class::find()->andWhere(['name' => $model->name])->exists()) {
            $model->save();
        }
    }

    protected function importArray(array $data, string $class, &$i, &$totalCount)
    {
        foreach ($data as $item) {
            $this->saveModel($item, $class);
            $i++;
            \yii\helpers\Console::updateProgress($i, $totalCount, 'Load data...');
        }
    }

    protected function importData()
    {
        $diseases = $this->getAutoimmuneDiseases();
        $symptoms = $this->getSymptoms();
        $healthGoals = $this->getHealthGoals();
        $totalCount = count($diseases) + count($symptoms) + count($healthGoals);
        $i = 0;

        \yii\helpers\Console::startProgress($i, $totalCount, 'Load data...');
        $this->importArray($diseases, AutoimmuneDisease::class, $i, $totalCount);
        $this->importArray($symptoms, Symptom::class, $i, $totalCount);
        $this->importArray($healthGoals, HealthGoal::class, $i, $totalCount);

        \yii\helpers\Console::endProgress();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->tableNames as $tableName) {
            $this->createTable($tableName, [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'description' => $this->text()->null(),
                'slug' => $this->string(),
                'createdAt' => $this->dateTime(),
                'updatedAt' => $this->dateTime()->null(),
            ]);
        }

        $this->importData();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->tableNames as $tableName) {
            $this->dropTable($tableName);
        }
    }

    protected function getHealthGoals()
    {
        return json_decode(file_get_contents(\Yii::getAlias('@console/migrations/data/healthGoals.json')));
    }

    protected function getAutoimmuneDiseases()
    {
        return json_decode(file_get_contents(\Yii::getAlias('@console/migrations/data/autoimmuneDiseases.json')));
    }

    protected function getSymptoms()
    {
        return json_decode(file_get_contents(\Yii::getAlias('@console/migrations/data/symptoms.json')));
    }
}
