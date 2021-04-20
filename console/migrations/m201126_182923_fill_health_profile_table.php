<?php

use yii\db\Migration;

/**
 * Class m201126_182923_fill_health_profile_table
 */
class m201126_182923_fill_health_profile_table extends Migration
{
    protected $tableName = 'health_profile';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rows = (new \yii\db\Query())
            ->select(['id'])
            ->from('account')
            ->where(['roleId' => 1])
            ->andWhere([
                'not in',
                'id',
                (new \yii\db\Query())
                    ->select('accountId')
                    ->from($this->tableName)
                    ->andWhere(['isMain' => 1])
            ])
            ->all();

        foreach ($rows as $row) {
            $this->insert(
                $this->tableName, [
                    'accountId' => $row['id'],
                    'isMain' => true,
                    'createdAt' => new \yii\db\Expression('NOW()'),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201126_182923_fill_health_profile_table cannot be reverted.\n";

        return false;
    }
}
