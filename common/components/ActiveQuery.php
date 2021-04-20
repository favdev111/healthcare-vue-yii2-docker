<?php

namespace common\components;

/**
 * Class ActiveQuery
 * @package common\components
 * @property prototype
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    public $tableName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $modelClass = $this->modelClass;
        $this->tableName = $modelClass::tableName();

        parent::init();
    }

    /**
     * @return static
     */
    public function getPrototype()
    {
        return clone $this;
    }
}
