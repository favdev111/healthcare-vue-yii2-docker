<?php

namespace modules\account\behaviors;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * Class PositionBehavior
 * @package modules\account\behaviors
 */
class PositionBehavior extends Behavior
{
    /**
     * @var string
     */
    public $positionAttribute = 'position';

    /**
     * @var array
     */
    public $groupAttributes = [];

    /**
     * @var
     */
    private $positionOnSave;

    /**
     * @return bool
     */
    public function movePrev(): bool
    {
        $positionAttribute = $this->positionAttribute;
        /* @var $previousRecord BaseActiveRecord */
        $previousRecord = $this->owner->find()
            ->andWhere($this->createGroupConditionAttributes())
            ->andWhere([$positionAttribute => ($this->owner->$positionAttribute - 1)])
            ->one();
        if (empty($previousRecord)) {
            return false;
        }
        $previousRecord->updateAttributes([
            $positionAttribute => $this->owner->$positionAttribute,
        ]);

        $this->owner->updateAttributes([
            $positionAttribute => $this->owner->$positionAttribute - 1,
        ]);
        return true;
    }

    /**
     * @return bool
     */
    public function moveNext(): bool
    {
        $positionAttribute = $this->positionAttribute;
        /* @var $nextRecord BaseActiveRecord */
        $nextRecord = $this->owner->find()
            ->andWhere($this->createGroupConditionAttributes())
            ->andWhere([$positionAttribute => ($this->owner->$positionAttribute + 1)])
            ->one();
        if (empty($nextRecord)) {
            return false;
        }
        $nextRecord->updateAttributes([
            $positionAttribute => $this->owner->$positionAttribute,
        ]);
        $this->owner->updateAttributes([
            $positionAttribute => $this->owner->getAttribute($positionAttribute) + 1,
        ]);
        return true;
    }

    /**
     * @return bool
     */
    public function moveFirst(): bool
    {
        $positionAttribute = $this->positionAttribute;
        if ($this->owner->$positionAttribute == 1) {
            return false;
        }
        $this->owner->updateAllCounters(
            [
                $positionAttribute => +1,
            ],
            [
                'and',
                $this->createGroupConditionAttributes(),
                ['<', $positionAttribute, $this->owner->$positionAttribute]
            ]
        );
        $this->owner->updateAttributes([
            $positionAttribute => 1,
        ]);
        return true;
    }

    /**
     * @return bool
     */
    public function moveLast(): bool
    {
        $positionAttribute = $this->positionAttribute;
        $recordsCount = $this->countGroupRecords();
        if ($this->owner->getAttribute($positionAttribute) == $recordsCount) {
            return false;
        }
        $this->owner->updateAllCounters(
            [
                $positionAttribute => -1,
            ],
            [
                'and',
                $this->createGroupConditionAttributes(),
                ['>', $positionAttribute, $this->owner->$positionAttribute]
            ]
        );
        $this->owner->updateAttributes([
            $positionAttribute => $recordsCount,
        ]);
        return true;
    }

    /**
     * @param $position
     * @return bool
     */
    public function moveToPosition($position): bool
    {
        if (!is_numeric($position) || $position < 1) {
            return false;
        }
        $positionAttribute = $this->positionAttribute;
        $oldRecord = $this->owner->findOne($this->owner->getPrimaryKey());
        $oldRecordPosition = $oldRecord->$positionAttribute;
        if ($oldRecordPosition == $position) {
            return true;
        }
        if ($position < $oldRecordPosition) {
            $this->owner->updateAllCounters(
                [
                    $positionAttribute => +1,
                ],
                [
                    'and',
                    $this->createGroupConditionAttributes(),
                    ['>=', $positionAttribute, $position],
                    ['<', $positionAttribute, $oldRecord->$positionAttribute],
                ]
            );
            $this->owner->updateAttributes([
                $positionAttribute => $position,
            ]);
            return true;
        }
        $recordsCount = $this->countGroupRecords();
        if ($position >= $recordsCount) {
            return $this->moveLast();
        }
        $this->owner->updateAllCounters(
            [
                $positionAttribute => -1,
            ],
            [
                'and',
                $this->createGroupConditionAttributes(),
                ['>', $positionAttribute, $oldRecord->$positionAttribute],
                ['<=', $positionAttribute, $position],
            ]
        );
        $this->owner->updateAttributes([
            $positionAttribute => $position,
        ]);
        return true;
    }

    /**
     * @return array
     */
    protected function createGroupConditionAttributes()
    {
        $condition = [];
        if (!empty($this->groupAttributes)) {
            foreach ($this->groupAttributes as $attribute) {
                $condition[$attribute] = $this->owner->$attribute;
            }
        }
        return $condition;
    }

    /**
     * @return mixed
     */
    protected function countGroupRecords()
    {
        $query = $this->owner->find();
        if (!empty($this->groupAttributes)) {
            $query->andWhere($this->createGroupConditionAttributes());
        }
        return $query->count();
    }

    /**
     * @return bool
     */
    public function getIsFirst(): bool
    {
        return $this->owner->getAttribute($this->positionAttribute) == 1;
    }

    /**
     * @return bool
     */
    public function getIsLast(): bool
    {
        $position = $this->owner->getAttribute($this->positionAttribute);
        if ($position === null) {
            return false;
        }
        return ($position >= $this->countGroupRecords());
    }

    /**
     * @return |null
     */
    public function findPrev()
    {
        if ($this->getIsFirst()) {
            return null;
        }
        $position = $this->owner->getAttribute($this->positionAttribute);
        $query = $this->owner->find();
        if (!empty($this->groupAttributes)) {
            $query->andWhere($this->createGroupConditionAttributes());
        }
        $query->andWhere([$this->positionAttribute => $position - 1]);
        return $query->one();
    }

    /**
     * @return mixed
     */
    public function findNext()
    {
        $position = $this->owner->getAttribute($this->positionAttribute);
        $query = $this->owner->find();
        if (!empty($this->groupAttributes)) {
            $query->andWhere($this->createGroupConditionAttributes());
        }
        $query->andWhere([$this->positionAttribute => $position + 1]);
        return $query->one();
    }

    /**
     * @return \yii\base\Component|null
     */
    public function findFirst()
    {
        if ($this->getIsFirst()) {
            return $this->owner;
        }
        $query = $this->owner->find();
        if (!empty($this->groupAttributes)) {
            $query->andWhere($this->createGroupConditionAttributes());
        }
        $query->andWhere([$this->positionAttribute => 1]);
        return $query->one();
    }

    /**
     * @return mixed
     */
    public function findLast()
    {
        $query = $this->owner->find();
        if (!empty($this->groupAttributes)) {
            $query->andWhere($this->createGroupConditionAttributes());
        }
        $query->orderBy([$this->positionAttribute => SORT_DESC])
            ->limit(1);
        return $query->one();
    }

    /**
     * @return array
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * @param $event
     */
    public function beforeInsert($event)
    {
        $positionAttribute = $this->positionAttribute;
        if ($this->owner->$positionAttribute > 0) {
            $this->positionOnSave = $this->owner->$positionAttribute;
        }
        $this->owner->$positionAttribute = $this->countGroupRecords() + 1;
    }

    /**
     * @param $event
     */
    public function beforeUpdate($event)
    {
        $positionAttribute = $this->positionAttribute;
        $isNewGroup = false;
        foreach ($this->groupAttributes as $groupAttribute) {
            if ($this->owner->isAttributeChanged($groupAttribute, false)) {
                $isNewGroup = true;
                break;
            }
        }
        if ($isNewGroup) {
            $oldRecord = $this->owner->findOne($this->owner->getPrimaryKey());
            $oldRecord->moveLast();
            $this->positionOnSave = $this->owner->$positionAttribute;
            $this->owner->$positionAttribute = $this->countGroupRecords() + 1;
        } else {
            if ($this->owner->isAttributeChanged($positionAttribute, false)) {
                $this->positionOnSave = $this->owner->$positionAttribute;
                $this->owner->$positionAttribute = $this->owner->getOldAttribute($positionAttribute);
            }
        }
    }

    /**
     * @param $event
     */
    public function afterSave($event)
    {
        if ($this->positionOnSave !== null) {
            $this->moveToPosition($this->positionOnSave);
        }
        $this->positionOnSave = null;
    }

    /**
     * @param $event
     */
    public function beforeDelete($event)
    {
        $this->moveLast();
    }
}
