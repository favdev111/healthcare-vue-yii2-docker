<?php

namespace modules\account\models\forms\professional\specifications;

use api2\helpers\DoctorType;
use api2\helpers\ProfessionalType;
use backend\models\BaseForm;
use backend\models\CompositeForm;
use common\components\validators\NPIValidator;
use common\models\health\AutoimmuneDisease;
use common\models\health\HealthGoal;
use common\models\health\HealthTest;
use common\models\health\MedicalCondition;
use common\models\health\Symptom;
use modules\account\models\Account;
use modules\account\models\ar\AccountAutoimmuneDisease;
use modules\account\models\ar\AccountHealthGoal;
use modules\account\models\ar\AccountHealthTest;
use modules\account\models\ar\AccountLicenceState;
use modules\account\models\ar\AccountMedicalCondition;
use modules\account\models\ar\AccountSymptom;
use modules\account\models\ar\AccountTelehealthState;
use modules\account\models\ar\State;
use modules\account\models\Profile;
use Yii;
use yii\base\ErrorException;
use yii\db\ActiveRecord;
use yii\db\conditions\AndCondition;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\validators\ExistValidator;
use yii\validators\InlineValidator;

/**
 * Class ProfessionalSpecificationsForm
 * @package modules\account\models\forms\professional\specifications
 *
 * @property ProfessionalSpecificationsOption $option
 */
class ProfessionalSpecificationsForm extends BaseForm
{
    /**
     * @var array
     */
    public $healthTests = [];
    /**
     * @var array
     */
    public $symptoms = [];
    /**
     * @var array
     */
    public $medicalConditions = [];
    /**
     * @var array
     */
    public $healthGoals = [];
    /**
     * @var array
     */
    public $autoimmuneDiseases = [];
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var ProfessionalSpecificationsOption
     */
    protected ProfessionalSpecificationsOption $option;

    /**
     * ProfessionalRoleForm constructor.
     * @param Account $account
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(Account $account, $config = [])
    {
        $this->account = $account;
        if (!isset($this->option)) {
            $this->option = Yii::createObject(ProfessionalSpecificationsOption::class);
        }
        $this->account = $account;
        parent::__construct($config);
    }

    public function init(): void
    {
        $this->healthTests = $this->account->getHealthTests()->select('id')->column();
        $this->symptoms = $this->account->getSymptoms()->select('id')->column();
        $this->medicalConditions = $this->account->getMedicalConditions()->select('id')->column();
        $this->healthGoals = $this->account->getHealthGoals()->select('id')->column();
        $this->autoimmuneDiseases = $this->account->getAutoimmuneDiseases()->select('id')->column();
    }

    public function rules()
    {
        return [
            [
                ['healthTests', 'symptoms', 'medicalConditions', 'healthGoals', 'autoimmuneDiseases'],
                'filter',
                'filter' => function ($value) {
                    if (empty($value)) {
                        return [];
                    }
                    return $value;
                }
            ],
            [
                'healthTests',
                'listValidate',
                'skipOnEmpty' => true,
                'params' => [
                    'existValidator' => [
                        'targetClass' => HealthTest::class,
                    ],
                ]
            ],
            [
                'symptoms',
                'listValidate',
                'skipOnEmpty' => true,
                'params' => [
                    'existValidator' => [
                        'targetClass' => Symptom::class,
                    ],
                ]
            ],
            [
                'medicalConditions',
                'listValidate',
                'skipOnEmpty' => true,
                'params' => [
                    'existValidator' => [
                        'targetClass' => MedicalCondition::class,
                    ],
                ]
            ],
            [
                'healthGoals',
                'listValidate',
                'skipOnEmpty' => true,
                'params' => [
                    'existValidator' => [
                        'targetClass' => HealthGoal::class,
                    ],
                ]
            ],
            [
                'autoimmuneDiseases',
                'listValidate',
                'skipOnEmpty' => true,
                'params' => [
                    'existValidator' => [
                        'targetClass' => AutoimmuneDisease::class,
                    ],
                ]
            ],
        ];
    }

    /**
     * @param $attribute
     * @return void|null
     * @throws \yii\base\InvalidConfigException
     */
    public function listValidate($attribute, $params)
    {
        $items = $this->{$attribute};
        $message = Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]);

        if (!$items || !is_array($items)) {
            $this->addError($attribute, $message);
            return;
        }

        $existValidator = ArrayHelper::getValue($params, 'existValidator');
        ArrayHelper::setValue($existValidator, 'targetAttribute', 'id');
        /** @var ExistValidator $existValidator */
        $existValidator = Instance::ensure($existValidator, ExistValidator::class);

        foreach ($items as $item) {
            if (!$existValidator->validate($item)) {
                $this->addError($attribute, $message);
                return;
            }
        }
    }

    /**
     * @return ProfessionalSpecificationsOption
     */
    public function getOption(): ProfessionalSpecificationsOption
    {
        return $this->option;
    }

    /**
     * @return Account|null
     * @throws ErrorException
     * @throws \yii\db\Exception
     */
    public function save(): ?Account
    {
        if (!$this->validate()) {
            return null;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->buildList($this->account, $this->healthTests, 'healthTestId', AccountHealthTest::class);
            $this->buildList($this->account, $this->symptoms, 'symptomId', AccountSymptom::class);
            $this->buildList($this->account, $this->medicalConditions, 'medicalConditionId', AccountMedicalCondition::class);
            $this->buildList($this->account, $this->healthGoals, 'healthGoalId', AccountHealthGoal::class);
            $this->buildList($this->account, $this->autoimmuneDiseases, 'autoimmuneDiseaseId', AccountAutoimmuneDisease::class);

            $transaction->commit();
            return $this->account;
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * @param Account $account
     * @param array $items
     * @param string $itemColumn
     * @param string $buildClass
     * @return array
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildList(Account $account, array $items, string $itemColumn, string $buildClass): array
    {
        $newItems = [];

        foreach ($items as $item) {
            $itemEntity = $buildClass::find()
                ->where(['accountId' => $account->id])
                ->andWhere([$itemColumn => $item])
                ->one();

            if ($itemEntity) {
                $newItems[] = $itemEntity;
            } else {
                $newItems[] = $this->buildItem($account, $item, $itemColumn, $buildClass);
            }
        }

        $ids = ArrayHelper::getColumn($newItems, 'id', []);
        $buildClass::deleteAll(new AndCondition([
            ['NOT IN', 'id', $ids],
            ['accountId' => $account->id]
        ]));

        return $newItems;
    }

    /**
     * @param Account $account
     * @param $value
     * @param string $itemColumn
     * @param string $buildClass
     * @return AccountTelehealthState
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildItem(Account $account, $value, string $itemColumn, string $buildClass): ActiveRecord
    {
        $model = Yii::createObject($buildClass);
        $model->accountId = $account->id;
        $model->{$itemColumn} = $value;

        if (!$model->save()) {
            throw new ErrorException("{$buildClass} was not saved");
        }

        return $model;
    }
}
