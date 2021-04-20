<?php

namespace modules\account\models;

use common\components\HtmlPurifier;
use common\helpers\Url;
use modules\account\helpers\ConstantsHelper;
use modules\account\models\forms\BookTutorWizardForm;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use Yii;

/**
 * This is the model class for table "tutor_bookings".
 *
 * @property int $id
 * @property string $ip
 * @property int $step
 * @property int $accountId
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property string $phoneNumber
 * @property int $schoolGradeLevelId
 * @property string $subjects
 * @property string $note
 * @property string $startDate
 * @property string $duration
 * @property double $hourlyRate
 * @property integer $tutorId
 * @property integer $bookingCompanyId
 * @property object $paymentAdd
 * @property string $zipCode
 * @property string $gclid
 * @property string $advertisingChannel
 * @property string $source
 * @property string $isAccountAlreadyExists
 * @property bool $isTermsSigned
 * @property integer $timePreferences
 *
 * @property string $fullNote
 * @property string $durationString
 * @property string $timePreferencesLabel
 * @property array $mailNotificationContentArray
 * @property float $chargePrice
 * @property float $fullPrice
 */
class TutorBooking extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tutor_bookings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['ip'],
                'default',
                'value' => function () {
                    return \Yii::$app->request->userIP;
                }
            ],
            [['step'], 'default', 'value' => 1],
            [['ip', 'step', 'firstName', 'lastName', 'email', 'phoneNumber'], 'required'],
            [['step', 'accountId', 'schoolGradeLevelId', 'tutorId', 'duration', 'timePreferences'], 'integer'],
            [['note', 'zipCode', 'gclid', 'source'], 'string'],
            [['startDate', 'paymentAdd',  'advertisingChannel'], 'safe'],
            [['hourlyRate'], 'number'],
            [['ip', 'firstName', 'lastName', 'email', 'phoneNumber', 'subjects'], 'string', 'max' => 255],
            [
                ['firstName', 'lastName', 'phoneNumber', 'email', 'note'],
                'filter',
                'filter' => function ($value) {
                    return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
                }
             ],
            ['isTermsSigned', 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'step' => 'Step',
            'accountId' => 'Account ID',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'email' => 'Email',
            'phoneNumber' => 'Phone Number',
            'schoolGradeLevelId' => 'School Grade Level ID',
            'subjects' => 'Subjects',
            'note' => 'Note',
            'startDate' => 'Start Date',
            'duration' => 'Duration',
            'hourlyRate' => 'Rate',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\query\TutorBookingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\TutorBookingQuery(get_called_class());
    }

    public static function getDurationData()
    {
        return [
            60 => '1h',
            90 => '1h 30m',
            120 => '2h',
            150 => '2h 30m',
            180 => '3h',
        ];
    }


    public static $timePreferencesList = [
        'Morning' => 1,
        'Afternoon' => 2,
        'Evening' => 3,
    ];

    /**
     * @return string
     */
    public function getTimePreferencesLabel(): string
    {
        return array_search($this->timePreferences, static::$timePreferencesList);
    }


    public function getDurationString(): string
    {
        return static::getDurationData()[$this->duration];
    }

    /**
     * @return string
     */
    public function getFullNote(): string
    {
        $subjectOrCategoryModel = SubjectOrCategory::findById($this->subjects);
        return 'This client booked a tutor from online landing modal.'
            . ($subjectOrCategoryModel->getName() ? "\nSUBJECT: " . $subjectOrCategoryModel->getName() : '')
            . ($this->startDate ? ("\nSTART DATE: " . $this->startDate) : '')
            . ($this->schoolGradeLevelId
                ? (
                    "\nSCHOOL GRADE LEVEL: " . ($this->schoolGradeLevelId
                        ? Profile::getSchoolGradeLevelArray()[$this->schoolGradeLevelId]
                        : '')
                )
                : '')
            . ($this->timePreferences ? "\nTIME PREFERENCES: " . ($this->timePreferencesLabel ?? '') : '')
            . ($this->hourlyRate ? ("\nRATE: " . $this->hourlyRate) : '')
            . ($this->note ? ("\nNOTE: " . $this->note) : '');
    }

    public function getMailNotificationContentArray(): array
    {
        $subjectOrCategory = SubjectOrCategory::findById($this->subjects);
        $contentArray = [
            'Ip' => $this->ip,
            'Name' => $this->firstName . ' ' . $this->lastName,
            'Email' => $this->email,
            'Phone' => $this->phoneNumber,
            'SchoolGradeLevel' => ConstantsHelper::schoolGradeLevel()[$this->schoolGradeLevelId] ?? '',
            'Subject' => $subjectOrCategory->getName() ?? '',
            'StartDate' => $this->startDate,
            'TimePreferences' => $this->timePreferencesLabel ?? '',
            'HourlyRate' => $this->hourlyRate,
            'ZipCode' => $this->zipCode,
        ];
        if ($this->isAccountAlreadyExists && $this->accountId) {
            $contentArray['AccountLink'] = Url::toB2bRoute('/client/' . $this->accountId, []);
        }
        $contentArray['Note'] = $this->note;
        return $contentArray;
    }

    public function getChargePrice(): float
    {
        $package = BookTutorWizardForm::$packages[(int)$this->hourlyRate] ?? 0;
        if (empty($package)) {
            return 0;
        }
        return $package['hourlyRate'] * $package['hours'];
    }

    /**
     * Price without discount
     * @return float
     */
    public function getFullPrice(): float
    {
        $package = BookTutorWizardForm::$packages[(int)$this->hourlyRate] ?? 0;
        if (empty($package)) {
            return 0;
        }
        return BookTutorWizardForm::DEFAULT_PRICE_FOR_HOUR * $package['hours'];
    }

    /**
     * @return float|int
     */
    public function getConversionPrice()
    {
        return $this->fullPrice > 0 ? $this->fullPrice : (int)$this->hourlyRate;
    }
}
