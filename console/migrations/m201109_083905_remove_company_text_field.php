<?php

use yii\db\Migration;

/**
 * Class m201109_083905_remove_company_text_field
 */
class m201109_083905_remove_company_text_field extends Migration
{
    public $tableNameProfile = '{{%account_profile}}';
    public $column = 'insuranceCompanyText';
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
        echo "m201109_083905_remove_company_text_field cannot be reverted.\n";

        return false;
    }
}
