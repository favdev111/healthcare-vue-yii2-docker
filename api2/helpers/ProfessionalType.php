<?php

namespace api2\helpers;

use yii\helpers\ArrayHelper;

final class ProfessionalType
{
    public const DOCTOR = 1;
    public const HOLISTIC_NURSE = 2;
    public const NURSE_PRACTITIONER = 3;
    public const REGISTERED_NURSE = 4;
    public const REGISTERED_DIETITIAN = 5;
    public const CHIROPRACTOR = 6;
    public const NUTRITIONIST = 7;
    public const PSYCHOLOGIST = 8;

    public const LABELS = [
        self::DOCTOR => 'Doctor',
        self::HOLISTIC_NURSE => 'Holistic nurse',
        self::REGISTERED_NURSE => 'Registered nurse',
        self::NURSE_PRACTITIONER => 'Nurse practitioner',
        self::REGISTERED_DIETITIAN => 'Registered dietitian',
        self::CHIROPRACTOR => 'Chiropractor',
        self::NUTRITIONIST => 'Nutritionist',
        self::PSYCHOLOGIST => 'Psychologist',
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
