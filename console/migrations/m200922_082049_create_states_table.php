<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%states}}`.
 */
class m200922_082049_create_states_table extends Migration
{
    public $stateTableName = '{{%state}}';
    public $cityTableName = '{{%location_city}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->stateTableName, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(),
            'shortName' => $this->string(),
            'slug' => $this->string(),
        ]);

        $this->dropColumn($this->cityTableName, 'stateName');
        $this->dropColumn($this->cityTableName, 'stateNameShort');
        $this->addColumn($this->cityTableName, 'stateId', $this->integer()->unsigned());

        Yii::$app->runAction('location/update');

        $this->addForeignKey('city_stateId_state', $this->cityTableName, 'stateId', $this->stateTableName, 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
