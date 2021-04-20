<?php

namespace modules\account\models\backend;

use api2\helpers\DoctorType;
use api2\helpers\EnrolledTypes;
use api2\helpers\ProfessionalType;
use common\models\health\AutoimmuneDisease;
use common\models\health\HealthGoal;
use common\models\health\HealthTest;
use common\models\health\MedicalCondition;
use common\models\health\Symptom;
use common\models\Zipcode;
use modules\account\models\ar\AccountEducation;
use modules\account\models\ar\AccountRate;
use modules\account\models\ar\State;
use modules\account\models\EducationCollege;
use modules\account\models\Profile;
use yii\base\InvalidArgumentException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class AccountProfessionalSearch
 * @package modules\account\models\backend
 *
 * @property-read mixed $college
 */
class AccountProfessionalSearch extends AccountSearch
{
    const PAGE_SIZE = 10;

    public $professionalTypeId;
    public $healthTests;
    public $symptoms;
    public $medicalConditions;
    public $autoimmuneDiseases;
    public $healthGoals;
    public $telehealthStates;
    public $currentlyEnrolled;
    public $doctorTypeId;
    public $zipCode;
    public $gender;
    public $phoneNumber;
    public $collegeId;
    public $keyword;
    public $educations;
    public $minRange;
    public $maxRange;
    public $duration;

    public function formName()
    {
        return 'as';
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [[
                    'healthTests',
                    'symptoms',
                    'medicalConditions',
                    'autoimmuneDiseases',
                    'healthGoals',
                    'telehealthStates',
                    'educations',
                    'minRange',
                    'maxRange',
                    'duration',
                ], 'safe'
                ],
                ['currentlyEnrolled', 'in', 'range' => array_keys(EnrolledTypes::LABELS)],
                ['doctorTypeId', 'in', 'range' => array_keys(DoctorType::DOCTORS_SPECIALIZATION_LABELS)],
                ['professionalTypeId', 'in', 'range' => array_keys(ProfessionalType::getAllTypes())],
                ['gender', 'in', 'range' => array_keys(Profile::getGenderArray())],
                [['createdAt', 'keyword', 'phoneNumber'], 'string', 'max' => 255],
                [['id', 'zipCode'], 'number'],
                ['zipCode', 'exist', 'targetClass' => Zipcode::class, 'targetAttribute' => 'code'],
            ]
        );
    }

    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['passwordHash']);

        $additional = [
            'profile' => function ($model) {
                return $model->profile;
            },
            'rate' => function ($model) {
                return $model->rate;
            },
            'rating' => function ($model) {
                return $model->rating;
            },
            'doctorType' => function ($model) {
                return DoctorType::getDoctorType($model->profile->doctorTypeId);
            },
            'photo' => function ($model) {
                return $model->hasPhoto ? $model->getSmallThumbnailUrl() : null;
            },
        ];
        return array_merge($fields, $additional);
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'currentlyEnrolled' => 'Medicare/Medicaid',
                'keyword' => 'Keyword',
                'doctorTypeId' => 'Doctor type',
                'professionalTypeId' => 'Professional type',
            ]
        );
    }

    /**
     * @param string $attribute
     * @return array
     * @throws \Exception
     */
    public function getSelectedItems(string $attribute)
    {
        $attributeToEntity = [
            'healthTests' => HealthTest::class,
            'symptoms' => Symptom::class,
            'medicalConditions' => MedicalCondition::class,
            'autoimmuneDiseases' => AutoimmuneDisease::class,
            'healthGoals' => HealthGoal::class,
            'telehealthStates' => State::class,
            'minRange' => AccountRate::class,
            'maxRange' => AccountRate::class,
            'duration' => AccountRate::class,
        ];
        $entityClass = ArrayHelper::getValue($attributeToEntity, $attribute);
        if (!$entityClass) {
            throw new InvalidArgumentException("Invalid attribute name. {$attribute} is not handled");
        }
        $value = $this->{$attribute};
        if (!$value) {
            return [];
        }
        /** @var ActiveRecord $entityClass */
        $aa = $entityClass::find()
            ->where(['id' => $value])
            ->indexBy('id')
            ->select(['name'])
            ->column();

        return $aa;
    }

    /**
     * @inheritdoc
     */
    public function search($params)
    {
        $dataProvider = $this->dataProvider(self::findWithoutRestrictions()->isSpecialist()->distinct(), $params);

        $dataProvider->sort->attributes['gender'] = [
            'asc' => ['p.gender' => SORT_ASC],
            'desc' => ['p.gender' => SORT_DESC],
        ];

        // frontend pagination
        if (isset($params['per-page']) || isset($params['page'])) {
            $dataProvider->pagination = [
                'pageSize' => isset($params['per-page']) ? $params['per-page'] : self::PAGE_SIZE,
                'page' => isset($params['page']) ? $params['page'] : 0,
            ];
        }


        if (!$this->validate()) {
            return $dataProvider;
        }

        $query = $dataProvider->query;

        $query->andFilterWhere([self::tableName() . '.id' => $this->id]);
        $query->andFilterWhere(['like', 'p.phoneNumber', $this->phoneNumber]);

        $query->andFilterWhere(['p.zipCode' => $this->zipCode]);

        if (!empty($this->gender) && $this->gender != 'b') {
            $query->andWhere(['p.gender' => $this->gender]);
        }

        if ($this->createdAt) {
            $query->andFilterWhere([
                '>=', self::tableName() . '.createdAt',
                \DateTime::createFromFormat('m/d/Y', $this->createdAt)->format('Y-m-d')
            ]);
        }

        if ($this->keyword) {
            $query
                ->andFilterWhere(['like', 'p.description', $this->keyword]);
        }

        $query->andFilterWhere(['professionalTypeId' => $this->professionalTypeId]);
        $query->andFilterWhere(['doctorTypeId' => $this->doctorTypeId]);
        if ($this->healthTests) {
            $query->joinWith(['healthTests'])->andFilterWhere(['healthTestId' => $this->healthTests]);
        }

        if ($this->symptoms) {
            $query->joinWith(['symptoms'])->andFilterWhere(['symptomId' => $this->symptoms]);
        }

        if ($this->medicalConditions) {
            $query->joinWith(['medicalConditions'])->andFilterWhere(['medicalConditionId' => $this->medicalConditions]);
        }

        if ($this->autoimmuneDiseases) {
            $query->joinWith(['autoimmuneDiseases'])->andFilterWhere(['autoimmuneDiseaseId' => $this->autoimmuneDiseases]);
        }
        if ($this->healthGoals) {
            $query->joinWith(['healthGoals'])->andFilterWhere(['healthGoalId' => $this->healthGoals]);
        }

        if ($this->telehealthStates) {
            $query->joinWith(['telehealthStates'])->andFilterWhere(['stateId' => $this->telehealthStates]);
        }

        if ($this->educations) {
            $query->joinWith(['educations'])->andFilterWhere(['degreeId' => $this->educations]);
        }

        if ($this->minRange) {
            $query->joinWith(['rate'])->andFilterWhere(['>=', 'hourlyRate', $this->minRange]);
        }

        if ($this->maxRange) {
            $query->joinWith(['rate'])->andFilterWhere(['<=', 'hourlyRate', $this->maxRange]);
        }

        if ($this->duration) {
            $condition = ['or'];

            foreach ($this->duration as $key) {
                $condition[] = ['>', 'rate' . $key, 0];
            }
            $query->joinWith(['rate'])->andFilterWhere($condition);
        }

        if (isset($params['minRating']) && isset($params['minRating'])) {
            $query->joinWith(['rating'])->andFilterWhere(['>=', 'totalRating', $params['minRating']]);
            $query->joinWith(['rating'])->andFilterWhere(['<', 'totalRating', $params['minRating']]);
        }

        $query->andFilterWhere(['currentlyEnrolled' => $this->currentlyEnrolled]);

        if (isset($params['status']) && $params['status']) {
            $query->andFilterWhere(['status' => $params['status']]);
        }

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function ratings($params)
    {
        $dataProvider = $this->dataProvider(self::findWithoutRestrictions()->isSpecialist()->distinct(), []);

        $query = $dataProvider->query;

        if ($params['min'] && $params['max']) {
            $query->joinWith(['rating'])->andFilterWhere(['>=', 'totalRating', $params['min']]);
            $query->joinWith(['rating'])->andFilterWhere(['<', 'totalRating', $params['max']]);
        }

        if ($params['status']) {
            $query->andFilterWhere(['status' => $params['status']]);
        }

        return $dataProvider;
    }
}
