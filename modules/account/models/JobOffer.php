<?php

namespace modules\account\models;

use api\components\rbac\Rbac;
use common\components\behaviors\TimestampBehavior;
use modules\account\models\query\AccountQuery;
use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%job_offer}}".
 *
 * @property integer $id
 * @property integer $tutorId
 * @property integer $jobId
 * @property string $amount
 * @property integer $type
 * @property integer $status
 * @property string $createdAt
 * @property string $updatedAt
 * @property string $shareContactInfo
 * @property integer $responsibleId
 *
 * @property-read Job $job
 * @property-read Account $tutor
 * @property integer $formattedAmount
 */
class JobOffer extends \yii\db\ActiveRecord
{
    const SCENARIO_UPDATE = 'update';

    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_DECLINED = 2;

    const TYPE_OFFERED_BY_COMPANY = 1;
    const TYPE_OFFERED_BY_TUTOR = 2;
    const MAX_AMOUNT_TUTOR = 210;
    const MIN_AMOUNT_TUTOR = 17;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job_offer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $isTutor = (!Yii::$app->user->isGuest && Yii::$app->user->identity->isTutor());
        // 17 + 15% = 20
        $minAmount = $isTutor ? self::MIN_AMOUNT_TUTOR : 20;
        // 212 (210) + 15% ~= 250
        $maxAmount = $isTutor ? self::MAX_AMOUNT_TUTOR : 250;
        return [
            [['tutorId', 'jobId', 'type', 'status'], 'integer'],
            [['shareContactInfo'], 'boolean'],
            [['shareContactInfo'], 'default', 'value' => false],
            [['amount', 'jobId', 'tutorId'], 'required'],
            'amount' => [['amount'], 'integer', 'min' => $minAmount, 'max' => $maxAmount],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['type', 'in', 'range' => [self::TYPE_OFFERED_BY_COMPANY, self::TYPE_OFFERED_BY_TUTOR]],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_DECLINED]],
            ['status', 'in', 'range' => [self::STATUS_CONFIRMED, self::STATUS_DECLINED], 'on' => self::SCENARIO_UPDATE],
            'jobExists' => [['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::class, 'targetAttribute' => ['jobId' => 'id']],
            [['tutorId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['tutorId' => 'id'], 'filter' => function ($query) {
                /**
                 * @var $query AccountQuery
                 */
                $query->isSpecialist();
            }
            ],
            ['jobId', 'validateJob'],
            'validateStatus' => ['status', 'validateStatus'],
            ['type', 'default', 'value' => function ($model) {
                if (Yii::$app->user->isGuest) {
                    return null;
                }
                if (Yii::$app->user->can(Rbac::PERMISSION_BASE_B2B_PERMISSIONS)) {
                    return self::TYPE_OFFERED_BY_COMPANY;
                }
                if (Yii::$app->user->identity->isTutor()) {
                    return self::TYPE_OFFERED_BY_TUTOR;
                }
                return null;
            }
            ],
            ['amount', 'validateMaximum'],
            /*write Tutor's rate to offer amount*/
            [
                'amount',
                function () {
                    $paymentModule = Yii::$app->getModule('payment');
                    $this->amount = $paymentModule->calcTutorRateFromCompanyRate($this->amount, 0);
                },
                'when' => function () {
                    return $this->isNewRecord && $this->type === static::TYPE_OFFERED_BY_COMPANY;
                }
            ]
        ];
    }

    public function validateMaximum()
    {
        if ($this->tutor->rate->displayRate <= $this->amount) {
            $this->addError('amount', 'You can not offer more than current rate.');
        }
    }

    public function isOfferedByTutor()
    {
        return $this->type === static::TYPE_OFFERED_BY_TUTOR;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach ($scenarios as $scenario) {
            $scenario['type'] = '!type';
        }
        $scenarios[self::SCENARIO_UPDATE] = [
            'status',
            'shareContactInfo',
        ];
        return $scenarios;
    }

    public function validateJob()
    {
        if (!$this->isNewRecord) {
            return true;
        }
        if ($this->jobHasHire()) {
            $this->addError('jobId', 'You can submit offers for jobs without accepted or declined hires only.');
            return false;
        }
        return true;
    }

    public function validateStatus()
    {
        if (Yii::$app->user->identity->isCrmAdmin()) {
            if (
                ($this->isNewRecord && $this->status !== self::STATUS_PENDING)
                || ($this->type === self::TYPE_OFFERED_BY_COMPANY && $this->status !== self::STATUS_PENDING)
            ) {
                    $this->addError('status', 'You can not approve or decline own offers.');
                    return false;
            }
        }

        if (Yii::$app->user->identity->isTutor()) {
            if (
                ($this->isNewRecord && $this->status !== self::STATUS_PENDING)
                || ($this->type === self::TYPE_OFFERED_BY_TUTOR && $this->status !== self::STATUS_PENDING)
            ) {
                $this->addError('status', 'You can not approve or decline own offers.');
                return false;
            }
        }

        return true;
    }

    public function jobHasHire()
    {
        return $this->job
            ->getJobHires()
            ->andWhere([JobHire::tableName() . '.tutorId' => $this->tutorId])
            ->exists();
    }

    public function getJobHire()
    {
        return $this->job
            ->getJobHires()
            ->andWhere([JobHire::tableName() . '.tutorId' => $this->tutorId])
            ->limit(1)
            ->one();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tutorId' => Yii::t('app', 'Tutor ID'),
            'jobId' => Yii::t('app', 'Job ID'),
            'amount' => Yii::t('app', 'Amount'),
            'type' => Yii::t('app', 'Type'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'responsibleId',
                'updatedByAttribute' => false,
                'value' => Yii::$app->user->id
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'jobId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(Account::class, ['id' => 'tutorId']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /**
         * @var $moduleAccount \modules\account\Module
         */
        $moduleAccount = \Yii::$app->getModule('account');

        if (isset($changedAttributes['status'])) {
            if ($this->status == self::STATUS_CONFIRMED) {
                $moduleAccount->eventOfferConfirmed($this);
            }
            if ($this->status == self::STATUS_DECLINED) {
                $moduleAccount->eventOfferDeclined($this);
            }
        }

        if ($insert) {
            $moduleAccount->eventNewOffer($this);
        }
    }

    public function getFormattedAmount()
    {
        return Yii::$app->formatter->asInteger($this->getFullAmount());
    }

    public function getAmountForCompany()
    {
        $paymentModule = Yii::$app->getModule('payment');
        return round($paymentModule->getAmountWithCompanyCommissionForOfferOrHire($this->amount, 0));
    }

    public function getAmountForTutor()
    {
        return round($this->amount);
    }

    public function getFullAmount()
    {
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isTutor()) {
            $result = $this->getAmountForTutor();
        } else {
            $result = $this->getAmountForCompany();
        }
        return $result;
    }
}
