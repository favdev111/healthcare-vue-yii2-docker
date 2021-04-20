<?php

namespace api2\helpers;

use yii\helpers\ArrayHelper;

final class EnrolledTypes
{
    const NA = 0;
    const MEDICARE = 1;
    const MEDICAID = 2;
    const BOTH = 3;

    public const LABELS = [
        self::MEDICARE => 'Medicare',
        self::MEDICAID => 'Medicaid',
        self::BOTH => 'Both',
        self::NA => 'N/A',
    ];

    public static function getAllTypes(): array
    {
        return array_keys(self::LABELS);
    }

    /**
     * @param $id
     * @return string|null
     * @throws \Exception
     */
    public static function getType($id): ?string
    {
        return ArrayHelper::getValue(self::LABELS, $id);
    }
}
