<?php

namespace common\helpers;

class AccountStatusHelper
{
    public const STATUS_CREATED = 0;
    public const STATUS_NEED_REVIEW = 1;
    public const STATUS_ACTIVE = 2;
    public const STATUS_BLOCKED = 66;
    public const STATUS_DELETED = 99;

    public static function statuesDefaultListForDropdown(): array
    {
        $statusesDefault = self::statuesDefault();
        $items = self::getAllStatuses();
        $options = [];
        foreach ($items as $id => $name) {
            $options[$id] = [
                'disabled' => !in_array($id, $statusesDefault),
            ];
        }

        return [
            'items' => $items,
            'options' => $options,
        ];
    }

    public static function statuesDefault(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_BLOCKED,
        ];
    }

    /**
     * @param bool $forBackend
     * @param null $role
     * @return array
     */
    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_CREATED => 'Created (Not finished the registration process)',
            self::STATUS_NEED_REVIEW => 'Under Review',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_DELETED => 'Deleted',
        ];
    }

    public static function getStatusName(int $status): string
    {
        $statusArray = AccountStatusHelper::getAllStatuses();

        return $statusArray[$status] ?? '';
    }
}
