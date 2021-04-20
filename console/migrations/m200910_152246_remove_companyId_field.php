<?php

use yii\db\Migration;

/**
 * Class m200910_152246_remove_companyId_field
 */
class m200910_152246_remove_companyId_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('account', 'companyAccountId');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
