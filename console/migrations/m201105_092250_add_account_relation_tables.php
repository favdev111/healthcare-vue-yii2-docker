<?php

use yii\db\Migration;

/**
 * Class m201105_092250_add_account_relation_tables
 */
class m201105_092250_add_account_relation_tables extends Migration
{
    public $healthTests = 'account_health_test';
    public $symptoms = 'account_symptom';
    public $medicalConditions = 'account_medical_condition';
    public $autoimmuneDisease = 'account_autoimmune_disease';
    public $healthGoals = 'account_health_goal';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->healthTests, [
            'id' => $this->primaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned(),
            'healthTestId' => $this->integer()->unsigned(),
        ]);
        $this->addForeignKey($this->healthTests . '_account', $this->healthTests, ['accountId'], 'account', 'id');

        $this->createTable($this->symptoms, [
            'id' => $this->primaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned(),
            'symptomId' => $this->integer()->unsigned(),
        ]);
        $this->addForeignKey($this->symptoms . '_account', $this->symptoms, ['accountId'], 'account', 'id');

        $this->createTable($this->medicalConditions, [
            'id' => $this->primaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned(),
            'medicalConditionId' => $this->integer()->unsigned(),
        ]);
        $this->addForeignKey($this->medicalConditions . '_account', $this->medicalConditions, ['accountId'], 'account', 'id');

        $this->createTable($this->autoimmuneDisease, [
            'id' => $this->primaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned(),
            'autoimmuneDiseaseId' => $this->integer()->unsigned(),
        ]);
        $this->addForeignKey($this->autoimmuneDisease . '_account', $this->autoimmuneDisease, ['accountId'], 'account', 'id');

        $this->createTable($this->healthGoals, [
            'id' => $this->primaryKey()->unsigned(),
            'accountId' => $this->integer()->unsigned(),
            'healthGoalId' => $this->integer()->unsigned(),
        ]);
        $this->addForeignKey($this->healthGoals . '_account', $this->healthGoals, ['accountId'], 'account', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
