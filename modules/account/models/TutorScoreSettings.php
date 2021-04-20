<?php

namespace modules\account\models;

use modules\account\models\query\TutorScoreSettingQuery;
use Yii;
use common\components\HtmlPurifier;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%tutor_score_settings}}".
 *
 * @property integer $id
 * @property string $key
 * @property integer $value
 * @property integer $type
 * @property string $createdAt
 * @property string $updatedAt
 */
class TutorScoreSettings extends \yii\db\ActiveRecord
{
    use CreateMultipleTrait;

    const TYPE_CONTENT_PROFILE = 1;
    const TYPE_RESPONSE_TIME = 2;
    const TYPE_HOURS = 3;
    const TYPE_RATING = 4;
    const TYPE_RECENT_ACTIVITY = 5;
    const TYPE_DISTANCE_SCORE = 6;
    const TYPE_AVAILABILITY_SCORE = 7;
    const TYPE_HOURS_PER_RELATION_SCORE = 8;
    const TYPE_REMATCHES_PER_MATCH_SCORE = 9;
    const TYPE_HOURS_PER_SUBJECT_SCORE = 10;
    const TYPE_REFUNDS_PER_MATCH_SCORE = 11;

    const TYPES = [self::TYPE_CONTENT_PROFILE, self::TYPE_RESPONSE_TIME, self::TYPE_HOURS, self::TYPE_RATING];

    public $min;
    public $max;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tutor_score_settings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['value'], 'integer'],
            [['key'], 'string', 'max' => 255],
            [['key'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
            'type' => 'Type',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    protected static function calculateScorePoints(
        $value,
        array $settings,
        string $betweenSymbol = '-',
        string $moreThanSymbol = '+'
    ) {
        if (is_null($value)) {
            $value = 0;
        }
        /**
         * @var TutorScoreSettings[] $settings
         */
        foreach ($settings as $setting) {
            if (strpos($setting->key, $betweenSymbol)) {
                $values = explode($betweenSymbol, $setting->key);
                if (((int)$values[0] <= $value) && ($value <= (int)$values[1])) {
                    return $coefficient = $setting->value;
                }
            } elseif (strpos($setting->key, $moreThanSymbol)) {
                if ((int)$setting->key < $value) {
                    return $coefficient = (int)$setting->value;
                }
            } elseif ((int)$setting->key == $value) {
                return $coefficient = $setting->value;
            }
        }
        return 0;
    }

    public static function getLastLoginCoefficient($hours)
    {
        $hours = (int)$hours;
        $settings = self::find()->mostRecentActivity()->all();
        return static::calculateScorePoints($hours, $settings) ?? 0;
    }

    public static function getDistanceScorePoints($distance)
    {
        $distance = (int)$distance;
        $settings = self::find()->distanceScore()->all();
        return static::calculateScorePoints($distance, $settings) ?? 0;
    }

    public static function getHoursPerRelationScorePoints($hoursPerRelation)
    {
        $hoursPerRelation = (int)$hoursPerRelation;
        $settings = self::find()->hoursPerRelation()->all();
        return static::calculateScorePoints($hoursPerRelation, $settings) ?? 0;
    }

    public static function getRematchPerMatchScorePoints(float $percent)
    {
        $settings = self::find()->rematchesPerMatch()->all();
        return static::calculateScorePoints($percent, $settings);
    }

    public static function getRefundPerMatchScorePoints(float $percent)
    {
        $settings = self::find()->refundsPerMatch()->all();
        return static::calculateScorePoints($percent, $settings);
    }

    public static function getHoursPerSubjectScorePoints($countHours)
    {
        $settings = self::find()->hoursPerSubject()->all();
        return static::calculateScorePoints($countHours, $settings);
    }

    public static function find()
    {
        return new TutorScoreSettingQuery(self::className());
    }

    public static function getLastVisitParamsArray()
    {
        return self::getArrayData('mostRecentActivity');
    }

    public static function getDistanceParamsArray()
    {
        return self::getArrayData('distanceScore');
    }

    public static function getHoursPerRelationParamsArray()
    {
        return self::getArrayData('hoursPerRelation');
    }

    public static function getAvailabilityParamsArray()
    {
        return self::getArrayData('availability');
    }

    protected static function getArrayData($scope)
    {
        $settings = self::find()->select(['key', 'value'])->$scope()->asArray()->all();
        return ArrayHelper::map($settings, 'key', 'value');
    }
}
