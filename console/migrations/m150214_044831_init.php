<?php

use yii\db\Migration;

class m150214_044831_init extends Migration
{
    public $tableName = '{{%user}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $initialDatabaseImporter = new \common\helpers\SqlImporter();
        $initialDatabaseImporter->filename = "@console/migrations/data/initial_database.sql";
        $initialDatabaseImporter->import();

        $this->insert(
            '{{%backend_account}}',
            [
                'email' => 'admin@admin.com',
                'passwordHash' => \Yii::$app->security->generatePasswordHash('st7cILm2E3P3rJV'),
                'firstName' => 'Admin',
                'lastName' => '',
                'roleId' => 5,
                'isActive' => true,
                'createdAt' => new \yii\db\Expression('NOW()'),
                'updatedAt' => new \yii\db\Expression('NOW()'),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
