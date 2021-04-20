<?php

use yii\db\Migration;

/**
 * Class m201104_104632_remove_licence_number_from_profile
 */
class m201104_104632_remove_licence_number_from_profile extends Migration
{
    public $tableNameProfile = '{{%account_profile}}';
    public $column = 'licenceNumber';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn($this->tableNameProfile, $this->column);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201104_104632_remove_licence_number_from_profile cannot be reverted.\n";

        return false;
    }
}
