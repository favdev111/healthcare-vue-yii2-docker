<?php

namespace modules\labels\models;

use InvalidArgumentException;

/**
 * Class LabelStatus
 * @package modules\labels\models
 */
final class LabelStatus
{
    /**
     *
     */
    private const STATUS_DRAFT = 0;

    /**
     *
     */
    private const STATUS_ACTIVE = 1;

    /**
     *
     */
    private const LIST_ALLOW_STATUS = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
    ];

    /**
     * @var int
     */
    private $status;

    /**
     * LabelStatus constructor.
     * @param int $status
     */
    public function __construct(int $status)
    {
        if (!in_array($status, self::LIST_ALLOW_STATUS)) {
            throw new InvalidArgumentException('Unknown label status:' . $status);
        }

        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * @return LabelStatus
     */
    public static function createActiveStatus(): self
    {
        return new self(self::STATUS_ACTIVE);
    }

    /**
     * @return LabelStatus
     */
    public static function createDraftStatus(): self
    {
        return new self(self::STATUS_DRAFT);
    }

    /**
     * @return array
     */
    public static function getAvailableStatusList(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DRAFT => 'Draft'
        ];
    }
}
