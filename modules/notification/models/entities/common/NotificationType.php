<?php

namespace modules\notification\models\entities\common;

use common\components\db\file\ActiveRecord;

/**
 * Class NotificationType
 * @package modules\notification\models\type
 *
 * @property-read int $id
 * @property-read string $name
 */
class NotificationType extends ActiveRecord
{
    /**
     * @var int
     */
    public const TYPE_CREDIT_CARD = 1;
    public const TYPE_SPECIALIST_ACCOUNT_APPROVED = 2;
    public const TYPE_FORGOT_PASSWORD = 3;
    public const TYPE_ACCOUNT_PATIENT_VERIFICATION = 4;
    public const TYPE_ACCOUNT_SPECIALIST_VERIFICATION = 5;

    /**
     * @return array|string
     */
    public static function fileName()
    {
        return 'modules/notification/data/NotificationType';
    }

    /**
     * @return static
     */
    public static function findCreditCard(): self
    {
        return self::findOne(self::TYPE_CREDIT_CARD);
    }

    /**
     * @return static
     */
    public static function findSpecialistAccountApproved(): self
    {
        return self::findOne(self::TYPE_SPECIALIST_ACCOUNT_APPROVED);
    }

    /**
     * @return static
     */
    public static function findForgotPassword(): self
    {
        return self::findOne(self::TYPE_FORGOT_PASSWORD);
    }

    /**
     * @return static
     */
    public static function findAccountPatientVerification(): self
    {
        return self::findOne(self::TYPE_ACCOUNT_PATIENT_VERIFICATION);
    }

    /**
     * @return static
     */
    public static function findAccountSpecialistVerification(): self
    {
        return self::findOne(self::TYPE_ACCOUNT_SPECIALIST_VERIFICATION);
    }
}
