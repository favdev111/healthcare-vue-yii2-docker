<?php

use yii\db\Migration;

/**
 * Class m200911_071518_remove_accountId_field_account_company_statistic
 */
class m200911_071518_remove_accountId_field_account_company_statistic extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk__account__account_company_statistic', 'account_company_statistic');
        $this->dropColumn('account_company_statistic', 'accountId');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
