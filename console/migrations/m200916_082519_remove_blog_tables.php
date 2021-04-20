<?php

use yii\db\Migration;

/**
 * Class m200916_082519_remove_blog_tables
 */
class m200916_082519_remove_blog_tables extends Migration
{
    public $tableNames = [
        'blog_post_share',
        'blog_post_tag',
        'blog_post',
        'blog_category',
        'tag',
    ];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->tableNames as $tableName) {
            $this->dropTable($tableName);
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
