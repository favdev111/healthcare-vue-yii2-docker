<?php

namespace modules\labels\models;

use InvalidArgumentException;

/**
 * Class LabelRelated
 * @package modules\labels\models
 */
final class LabelRelated
{
    /**
     *
     */
    private const TASK_LIST_RELATION = 'task-list';

    /**
     *
     */
    private const LIST_ALLOW_RELATION = [
        self::TASK_LIST_RELATION
    ];
    /**
     * @var string
     */
    private $relation;

    /**
     * LabelRelated constructor.
     * @param string $relation
     */
    public function __construct(string $relation)
    {
        if (!in_array($relation, self::LIST_ALLOW_RELATION)) {
            throw new InvalidArgumentException('Unknown label relation:' . $relation);
        }

        $this->relation = $relation;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @return bool
     */
    public function isTaskListRelation(): bool
    {
        return $this->relation === self::TASK_LIST_RELATION;
    }

    /**
     * @return LabelRelated
     */
    public static function createTaskListRelation(): self
    {
        return new self(self::TASK_LIST_RELATION);
    }
}
