<?php

namespace common\models\healthProfile\health\medical;

use common\components\db\file\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class HealthMedicalType
 * @package common\models\healthProfile\health
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read string $stringId Unique identifier by string format
 *
 * Is methods
 * @method bool isSymptom()
 * @method bool isMedicalConditions()
 * @method bool isAutoImmune()
 * @method bool isAllergies()
 * @method bool isFoodIntolerances()
 * @method bool isLifestyleDiet()
 * @method bool isCurrentMedications()
 * @method bool isOther()
 * @method bool isHealthGoal()
 * @method bool isHealthConcern()
 * @method bool isAllergiesCategory()
 * @method bool isFoodIntolerancesCategory()
 *
 * Find methods
 * @method static self findSymptom()
 * @method static self findMedicalConditions()
 * @method static self findAutoImmune()
 * @method static self findAllergies()
 * @method static self findFoodIntolerances()
 * @method static self findLifestyleDiet()
 * @method static self findCurrentMedications()
 * @method static self findOther()
 * @method static self findHealthGoal()
 * @method static self findHealthConcern()
 * @method static self findAllergiesCategory()
 * @method static self findFoodIntolerancesCategory()
 */
class HealthMedicalType extends ActiveRecord
{
    /**
     * @var int
     */
    public const TYPE_SYMPTOM = 1;
    /**
     * @var int
     */
    public const TYPE_MEDICAL_CONDITIONS = 2;
    /**
     * @var int
     */
    public const TYPE_AUTO_IMMUNE = 3;
    /**
     * @var int
     */
    public const TYPE_ALLERGIES = 4;
    /**
     * @var int
     */
    public const TYPE_FOOD_INTOLERANCES = 5;
    /**
     * @var int
     */
    public const TYPE_LIFESTYLE_DIET = 6;
    /**
     * @var int
     */
    public const TYPE_CURRENT_MEDICATIONS = 7;
    /**
     * @var int
     */
    public const TYPE_OTHER = 8;
    /**
     * @var int
     */
    public const TYPE_HEALTH_GOAL = 9;
    /**
     * @var int
     */
    public const TYPE_HEALTH_CONCERN = 10;
    /**
     * @var int
     */
    public const TYPE_ALLERGIES_CATEGORY = 11;
    /**
     * @var int
     */
    public const TYPE_FOOD_INTOLERANCES_CATEGORY = 12;

    /**
     * Define magic methods for check type using $this->isType()
     */
    private const METHODS_IS = [
        'isSymptom' => self::TYPE_SYMPTOM,
        'isMedicalConditions' => self::TYPE_MEDICAL_CONDITIONS,
        'isAutoImmune' => self::TYPE_AUTO_IMMUNE,
        'isAllergies' => self::TYPE_ALLERGIES,
        'isFoodIntolerances' => self::TYPE_FOOD_INTOLERANCES,
        'isLifestyleDiet' => self::TYPE_LIFESTYLE_DIET,
        'isCurrentMedications' => self::TYPE_CURRENT_MEDICATIONS,
        'isOther' => self::TYPE_OTHER,
        'isHealthGoal' => self::TYPE_HEALTH_GOAL,
        'isHealthConcern' => self::TYPE_HEALTH_CONCERN,
        'isAllergiesCategory' => self::TYPE_ALLERGIES_CATEGORY,
        'isFoodIntolerancesCategory' => self::TYPE_FOOD_INTOLERANCES_CATEGORY,
    ];

    /**
     * Define magic methods for check type using $this->findType()
     */
    private const METHODS_FIND = [
        'findSymptom' => self::TYPE_SYMPTOM,
        'findMedicalConditions' => self::TYPE_MEDICAL_CONDITIONS,
        'findAutoImmune' => self::TYPE_AUTO_IMMUNE,
        'findAllergies' => self::TYPE_ALLERGIES,
        'findFoodIntolerances' => self::TYPE_FOOD_INTOLERANCES,
        'findLifestyleDiet' => self::TYPE_LIFESTYLE_DIET,
        'findCurrentMedications' => self::TYPE_CURRENT_MEDICATIONS,
        'findOther' => self::TYPE_OTHER,
        'findHealthGoal' => self::TYPE_HEALTH_GOAL,
        'findHealthConcern' => self::TYPE_HEALTH_CONCERN,
        'findAllergiesCategory' => self::TYPE_ALLERGIES_CATEGORY,
        'findFoodIntolerancesCategory' => self::TYPE_FOOD_INTOLERANCES_CATEGORY,
    ];

    /**
     * @return array|string
     */
    public static function fileName()
    {
        return 'common/data/healthProfile/health/medical/HealthMedicalType';
    }

    /**
     * @param $name
     * @param $arguments
     * @return HealthMedicalType|null
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        $typeId = ArrayHelper::getValue(self::METHODS_FIND, $name);
        if ($typeId) {
            return self::findOne($typeId);
        }
    }

    /**
     * @param string $name
     * @param array $params
     * @return bool|mixed
     * @throws \Exception
     */
    public function __call($name, $params)
    {
        $typeId = ArrayHelper::getValue(self::METHODS_IS, $name);
        if ($typeId) {
            return $this->id === $typeId;
        }
        return parent::__call($name, $params);
    }
}
