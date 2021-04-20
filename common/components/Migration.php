<?php

namespace common\components;

class Migration extends \yii\db\Migration
{
    /**
     * List options for table creation
     *
     * @param $driverName
     *
     * @return null|array
     */
    protected function getDefaultTableOptions($driverName)
    {
        $tableOptions = null;
        if ($this->db->driverName === $driverName) {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        return $tableOptions;
    }
}
