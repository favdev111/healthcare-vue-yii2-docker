<?php

namespace modules\account\models\api;

use modules\account\helpers\Timezone;
use modules\account\models\PhoneValidation;
use Yii;

/**
* @inheritdoc
 * @property integer $mainPhoneNumberType
 */
class ProfileClient extends \modules\account\models\Profile
{
    public static function rulesCommon()
    {
        return array_merge(
            parent::rulesCommon(),
            [
                [['schoolName'], 'required'],
                [['schoolName'], 'string', 'max' => 255],
                [['schoolGradeLevel'], 'string', 'max' => 255],
                [['schoolGradeLevelId'], 'in', 'range' => array_keys(static::getSchoolGradeLevelArray())],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
           'firstName',
           'lastName',
           'zipCode',
           'address',
           'phoneNumber',
           'mainPhoneNumberType',
           'gender',
           'schoolName',
           'schoolGradeLevelId',
           'schoolGradeLevel',
           'grade',
           'startDate' => function () {
            if (empty($this->startDate)) {
                return null;
            }
            return Timezone::staticConvertFromServerTimeZone($this->startDate, Yii::$app->formatter->MYSQL_DATE);
           },
        ];
    }
}
