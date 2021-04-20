<?php

use yii\db\Migration;

/**
 * Class m200910_085749_drop_faq_tables
 */
class m200910_085749_drop_tables extends Migration
{
    public $tableNames = [
        'faq_post',
        'faq_category',
        'redirect_url',
        'meta_tag_option',
    ];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->tableNames as $tableName) {
            if ($this->db->getTableSchema($tableName, true) !== null) {
                $this->dropTable($tableName);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
