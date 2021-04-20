<?php

use yii\db\Migration;

/**
 * Class m200914_072605_createdIp_null
 */
class m200914_072605_createdIp_null extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('account', 'createdIp', $this->string(45)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
