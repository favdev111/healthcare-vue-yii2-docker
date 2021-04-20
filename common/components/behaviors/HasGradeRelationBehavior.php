<?php

namespace common\components\behaviors;

use modules\account\models\Grade;
use modules\account\models\GradeItem;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Class HasStudentGradeRelationBehavior
 * @package common\components\behaviors
 */
class HasGradeRelationBehavior extends Behavior
{
    public $type;

    public function init()
    {
        if (empty($this->type)) {
            throw new InvalidConfigException('Not all behavior attributes has been set');
        }
        parent::init();
    }

    public function getGrade()
    {
        return $this->owner->hasOne(Grade::class, ['id' => 'gradeId'])
            ->viaTable(
                GradeItem::tableName(),
                ['itemId' => 'id'],
                function ($query) {
                    $query->onCondition(['itemType' => $this->type, 'deletedAt' => null]);
                }
            );
    }

    public function getGradeItem()
    {
        return $this->owner->hasOne(GradeItem::class, ['itemId' => 'id'])
            ->onCondition(['itemType' => $this->type, 'deletedAt' => null]);
    }

    /**
     * @return GradeItem
     */
    public function createGradeItem(): GradeItem
    {
        return new GradeItem(['itemId' => $this->owner->id, 'itemType' => $this->type]);
    }
}
