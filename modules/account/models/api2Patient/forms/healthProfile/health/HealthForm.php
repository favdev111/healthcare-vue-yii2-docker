<?php

namespace modules\account\models\api2Patient\forms\healthProfile\health;

use api2\components\models\forms\ApiBaseForm;
use common\models\healthProfile\health\medical\HealthMedicalType;
use common\models\healthProfile\health\MedicalAllergyGroup;
use modules\account\models\api2\AutoimmuneDisease;
use modules\account\models\api2\health\allergy\Allergy;
use modules\account\models\api2\health\allergy\AllergyCategory;
use modules\account\models\api2\health\LifestyleDiet;
use modules\account\models\api2\HealthGoal;
use modules\account\models\api2\MedicalCondition;
use modules\account\models\api2\Symptom;
use modules\account\models\api2Patient\entities\healthProfile\health\HealthDrink;
use modules\account\models\api2Patient\entities\healthProfile\health\HealthProfileHealth;
use modules\account\models\api2Patient\entities\healthProfile\health\HealthSmoke;
use modules\account\models\api2Patient\entities\healthProfile\health\medical\HealthProfileHealthMedical;
use modules\account\models\api2Patient\entities\healthProfile\HealthProfile;
use modules\account\models\api2Patient\entities\healthProfile\insurance\HealthProfileInsurance;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\conditions\AndCondition;
use yii\helpers\ArrayHelper;
use yii\validators\ExistValidator;
use yii\validators\StringValidator;

/**
 * Class CreateHealthForm
 * @package modules\account\models\api2Patient\forms\healthProfile\insurance
 *
 * @property-read HealthProfileHealth $healthProfileHealth
 */
class HealthForm extends ApiBaseForm
{
    public $isAcceptAgreement;
    public $symptoms = [];
    public $medicalConditions = [];
    public $autoImmuneDiseases = [];
    public $allergies = [];
    public $allergiesCategory = [];
    public $foodIntolerances = [];
    public $foodIntolerancesCategory = [];
    public $lifestyleDiet = [];
    public $currentMedications = [];
    public $other = [];
    public $smokeId;
    public $drinkId;
    public $isOtherSubstance;
    public $otherSubstanceText;
    public $healthConcerns = [];
    public $healthGoals = [];

    /**
     * @var HealthProfile
     */
    protected $healthProfile;

    /**
     * CreateHealthForm constructor.
     * @param HealthProfile $healthProfile
     * @param array $config
     */
    public function __construct(HealthProfile $healthProfile, array $config = [])
    {
        $this->healthProfile = $healthProfile;
        parent::__construct($config);
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function rules()
    {
        $stringValidator = Yii::createObject(['class' => StringValidator::class, 'min' => 1]);

        return [
            ['isAcceptAgreement', 'required', 'message' => 'You must agree to the terms'],
            [
                ['isAcceptAgreement', 'isOtherSubstance'],
                'boolean',
                'trueValue' => true,
                'falseValue' => false,
                'strict' => true
            ],
            [
                'otherSubstanceText',
                'string',
                'when' => function () {
                    return $this->isAcceptAgreement === true;
                },
            ],
            [
                'isAcceptAgreement',
                function ($attribute) {
                    $value = $this->{$attribute};
                    $message = 'You must agree to the terms';
                    $value ?: $this->addError($attribute, $message);
                },
            ],
            ['smokeId', 'exist', 'targetClass' => HealthSmoke::class, 'targetAttribute' => 'id'],
            ['drinkId', 'exist', 'targetClass' => HealthDrink::class, 'targetAttribute' => 'id'],
            [
                'symptoms',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(Symptom::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findSymptom()),
                    ],
                ],
            ],
            [
                'medicalConditions',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(MedicalCondition::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findMedicalConditions()),
                    ],
                ],
            ],
            [
                'autoImmuneDiseases',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(AutoimmuneDisease::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findAutoImmune()),
                    ],
                ],
            ],
            [
                'allergies',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(Allergy::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findAllergies()),
                    ],
                ],
            ],
            [
                'allergiesCategory',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(MedicalAllergyGroup::class, 'allergyCategoryId'),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findAllergiesCategory()),
                    ],
                ],
            ],
            [
                'foodIntolerancesCategory',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(MedicalAllergyGroup::class, 'allergyCategoryId'),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findFoodIntolerancesCategory()),
                    ],
                ],
            ],
            [
                'foodIntolerances',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(Allergy::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findFoodIntolerances()),
                    ],
                ],
            ],
            [
                'foodIntolerances',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(Allergy::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findFoodIntolerances()),
                    ],
                ],
            ],
            [
                'lifestyleDiet',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(LifestyleDiet::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findLifestyleDiet()),
                    ],
                ],
            ],
            [
                'healthGoals',
                'listsValidator',
                'params' => [
                    'uniqueValues' => true,
                    'add' => [
                        'validator' => $this->existListValidator(HealthGoal::class),
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findHealthGoal()),
                    ],
                ]
            ],
            [
                'currentMedications',
                'listsValidator',
                'params' => [
                    'add' => [
                        'validator' => $stringValidator,
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findCurrentMedications()),
                        'valueValidator' => $stringValidator,
                    ],
                ]
            ],
            [
                'healthConcerns',
                'listsValidator',
                'params' => [
                    'add' => [
                        'validator' => $stringValidator,
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findHealthConcern()),
                        'valueValidator' => $stringValidator,
                    ],
                ]
            ],
            [
                'other',
                'listsValidator',
                'params' => [
                    'add' => [
                        'validator' => $stringValidator,
                    ],
                    'update' => [
                        'idValidator' => $this->existHealthMedicalValidator(HealthMedicalType::findOther()),
                        'valueValidator' => $stringValidator,
                    ],
                ]
            ]
        ];
    }

    /**
     * @param string $targetClass
     * @param string $targetAttribute
     * @return ExistValidator
     * @throws InvalidConfigException
     */
    private function existListValidator(string $targetClass, string $targetAttribute = 'id'): ExistValidator
    {
        return Yii::createObject([
            'class' => ExistValidator::class,
            'targetClass' => $targetClass,
            'targetAttribute' => $targetAttribute,
        ]);
    }

    /**
     * @param HealthMedicalType $healthMedicalType
     * @return ExistValidator
     * @throws InvalidConfigException
     */
    private function existHealthMedicalValidator(HealthMedicalType $healthMedicalType): ExistValidator
    {
        return Yii::createObject([
            'class' => ExistValidator::class,
            'targetClass' => HealthProfileHealthMedical::class,
            'targetAttribute' => 'id',
            'filter' => [
                'AND',
                ['healthProfileId' => $this->healthProfile->id],
                ['medicalTypeId' => $healthMedicalType->id],
            ]
        ]);
    }

    /**
     * @param string $attribute
     * @param array $params
     * @return array
     * @throws InvalidConfigException|\Exception
     */
    public function listsValidator(string $attribute, array $params)
    {
        $value = $this->{$attribute};
        $addItems = ArrayHelper::getValue($value, 'add', []);
        $updateItems = ArrayHelper::getValue($value, 'update', []);
        if (!$addItems && !$updateItems) {
            return;
        }

        $message = Yii::t('yii', '{attribute} is invalid.', ['attribute' => $attribute]);
        $uniqueValues = ArrayHelper::getValue($params, 'uniqueValues', false);

        if ($uniqueValues) {
            $updateValues = ArrayHelper::getColumn($updateItems, 'value');
            $allItemsValues = array_merge($addItems, $updateValues);
            if (count(array_unique($allItemsValues)) < count($allItemsValues)) {
                $this->addError($attribute, $message . " Unique values");
                return;
            }
        }

        $addValidator = ArrayHelper::getValue($params, 'add.validator');

        foreach ($addItems as $addItem) {
            if (!is_scalar($addItem) || !$addValidator->validate($addItem)) {
                $this->addError($attribute, $message . " Add value ({$addItem})");
                return;
            }
        }

        $idValidator = ArrayHelper::getValue($params, 'update.idValidator');
        $valueValidator = ArrayHelper::getValue($params, 'update.valueValidator');

        foreach ($updateItems as $updateItem) {
            $updateId = ArrayHelper::getValue($updateItem, 'id');
            if (!$updateId || !$idValidator->validate($updateId)) {
                $this->addError($attribute, $message . " Update id ({$updateId})");
                return;
            }
            if ($valueValidator) {
                $updateValue = ArrayHelper::getValue($updateItem, 'value');
                if (!$updateValue || !$valueValidator->validate($updateValue)) {
                    $this->addError($attribute, $message . " Update value ({$updateId})");
                    return;
                }
            }
        }
    }

    /**
     * @return HealthProfileInsurance|null
     * @throws ErrorException
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function create(): ?HealthProfile
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->buildHPHealth($this->healthProfileHealth);
            $this->buildHPMedicals($this->symptoms, HealthMedicalType::findSymptom(), ['internalId']);
            $this->buildHPMedicals($this->medicalConditions, HealthMedicalType::findMedicalConditions(), ['internalId']);
            $this->buildHPMedicals($this->autoImmuneDiseases, HealthMedicalType::findAutoImmune(), ['internalId']);
            $this->buildHPMedicals($this->allergies, HealthMedicalType::findAllergies(), ['internalId']);
            $this->buildHPMedicals($this->allergiesCategory, HealthMedicalType::findAllergiesCategory(), ['internalId']);
            $this->buildHPMedicals($this->foodIntolerances, HealthMedicalType::findFoodIntolerances(), ['internalId']);
            $this->buildHPMedicals($this->foodIntolerancesCategory, HealthMedicalType::findFoodIntolerancesCategory(), ['internalId']);
            $this->buildHPMedicals($this->lifestyleDiet, HealthMedicalType::findLifestyleDiet(), ['internalId']);
            $this->buildHPMedicals($this->currentMedications, HealthMedicalType::findCurrentMedications(), ['text']);
            $this->buildHPMedicals($this->other, HealthMedicalType::findOther(), ['text']);
            $this->buildHPMedicals($this->healthConcerns, HealthMedicalType::findHealthConcern(), ['text']);
            $this->buildHPMedicals($this->healthGoals, HealthMedicalType::findHealthGoal(), ['internalId']);

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        return $this->healthProfile;
    }

    protected function getHealthProfileHealth()
    {
        return $this->healthProfile->healthProfileHealth ?? Yii::createObject(HealthProfileHealth::class);
    }

    /**
     * @param HealthProfileHealth $model
     * @return HealthProfileHealth
     * @throws ErrorException
     */
    protected function buildHPHealth(HealthProfileHealth $model): HealthProfileHealth
    {
        $model->healthProfileId = $this->healthProfile->id;
        $model->smokeId = $this->smokeId ?? $model->smokeId;
        $model->drinkId = $this->drinkId ?? $model->drinkId;
        $model->isOtherSubstance = $this->isOtherSubstance ?? $model->isOtherSubstance;

        if ($this->isOtherSubstance !== null) {
            if ($this->isOtherSubstance) {
                $model->otherSubstanceText = $this->otherSubstanceText;
            } else {
                $model->otherSubstanceText = null;
            }
        }

        if (!$model->save()) {
            throw new ErrorException('HealthProfileHealth was not saved');
        }

        return $model;
    }

    /**
     * @param array $items List of items, associative or indexed array in case associative set valueConditions multi
     * @param HealthMedicalType $healthMedicalType
     * @param array $valueConditions Example: ['columnName' => 'internalId', 'columnName' => 'text'] OR ['internalId']
     * @return HealthProfileHealthMedical[]
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildHPMedicals(
        array $items,
        HealthMedicalType $healthMedicalType,
        array $valueConditions
    ): array {
        $addValues = ArrayHelper::getValue($items, 'add');
        $updateValues = ArrayHelper::getValue($items, 'update');

        $valuesEmpty = (!$addValues && !$updateValues);

        // in case add and update keys are array and empty then delete all items for this type
        if (is_array($addValues) && is_array($updateValues) && $valuesEmpty) {
            HealthProfileHealthMedical::deleteAll(['medicalTypeId' => $healthMedicalType->id]);
            return [];
        }

        if (!$items || (!$addValues && !$updateValues)) {       // in case values is empty or is not set
            return [];
        }

        // set default values
        $addValues = $addValues ?? [];
        $updateValues = $updateValues ?? [];

        if (!$valueConditions) {
            throw new InvalidConfigException('valueConditions must be set');
        }
        $internalIdKey = array_search('internalId', $valueConditions, true);
        $textKey = array_search('text', $valueConditions, true);

        if ($internalIdKey !== false && $textKey !== false && !is_string($internalIdKey) && !is_string($textKey)) {
            throw new InvalidConfigException('valueConditions is invalid, at least one value may be without key');
        }

        $builtItems = ['add' => [], 'update' => []];

        // add values
        foreach ($addValues as $addItem) {
            $internalId = $this->getValueByKey($internalIdKey, $addItem);
            $text = $this->getValueByKey($textKey, $addItem);

            $model = Yii::createObject(HealthProfileHealthMedical::class);
            $builtItems['add'][] = $this->buildHPMedical($model, $healthMedicalType, $internalId, $text);
        }

        // update values
        foreach ($updateValues as $updateValue) {
            $id = ArrayHelper::getValue($updateValue, 'id');
            $value = ArrayHelper::getValue($updateValue, 'value');
            $internalId = $this->getValueByKey($internalIdKey, $value);
            $text = $this->getValueByKey($textKey, $value);

            $model = $this->healthProfile
                ->getHealthProfileHealthMedicals()
                ->where(['health_profile_health_medical.id' => $id])
                ->one();
            if (!$model) {
                throw new \InvalidArgumentException('Invalid Health Profile Medical model');
            }
            $builtItems['update'][] = $this->buildHPMedical($model, $healthMedicalType, $internalId, $text);
        }

        if ($builtItems['add'] || $builtItems['update']) {          // in case values was updated or added to database
            $addIds = ArrayHelper::getColumn($builtItems['add'], 'id', []);
            $updateIds = ArrayHelper::getColumn($builtItems['update'], 'id', []);
            $allIds = array_merge($addIds, $updateIds);

            // remove other values
            HealthProfileHealthMedical::deleteAll(new AndCondition([
                ['NOT IN', 'id', $allIds],
                ['medicalTypeId' => $healthMedicalType->id],
            ]));
        }

        return $builtItems;
    }

    /**
     * @param $key
     * @param $item
     * @return mixed|null
     * @throws \Exception
     */
    private function getValueByKey($key, $item)
    {
        if ($key !== false) {
            return is_string($key) ? ArrayHelper::getValue($item, $key) : $item;
        }
        return null;
    }

    /**
     * @param HealthProfileHealthMedical $model
     * @param HealthMedicalType $healthMedicalType
     * @param null $internalId
     * @param null $text
     * @return HealthProfileHealthMedical
     * @throws ErrorException
     */
    protected function buildHPMedical(
        HealthProfileHealthMedical $model,
        HealthMedicalType $healthMedicalType,
        $internalId = null,
        $text = null
    ): HealthProfileHealthMedical {
        if ($internalId === null && $text === null) {
            throw new InvalidArgumentException('At least one of params must be set (internalId OR text)');
        }

        $model->healthProfileId = $this->healthProfile->id;
        $model->text = $text;
        $model->internalId = $internalId;
        $model->medicalTypeId = $healthMedicalType->id;

        if (!$model->save()) {
            throw new ErrorException('HealthProfileHealthMedical was not saved');
        }

        return $model;
    }
}
