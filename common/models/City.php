<?php

namespace common\models;

use common\helpers\LandingPageHelper;
use Exception;
use modules\account\models\Account;
use modules\account\models\api2\State;
use modules\account\models\UpdateAllSlugsTrait;
use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "{{%location_city}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $stateName
 * @property string $stateNameShort
 * @property string $stateNameSlug
 * @property string $createdAt
 *
 * @property string $slug
 * @property string $latitude
 * @property string $longitude
 * @property string timeZone
 * @property-read  State $state
 */
class City extends \yii\db\ActiveRecord
{
    use UpdateAllSlugsTrait;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%location_city}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'updatedAtAttribute' => null,
            ],
            'slug' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'slug',
                'ensureUnique' => false,
                'value' => function () {
                    if ($this->slug) {
                        return $this->slug;
                    }

                    return LandingPageHelper::slug($this->name);
                },
            ],
            'stateNameSlug' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'stateNameSlug',
                'ensureUnique' => false,
                'value' => function () {
                    if ($this->stateNameSlug) {
                        return $this->stateNameSlug;
                    }

                    return LandingPageHelper::slug($this->stateName);
                },
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZipcodes()
    {
        return $this->hasMany(Zipcode::class, ['cityId' => 'id']);
    }

    public static function getAllStateSlugs()
    {
        $returnArray = [];
        $slugs = self::find()->select(['stateNameSlug', 'stateName'])->asArray()->all();
        if (!empty($slugs)) {
            foreach ($slugs as $slug) {
                if (!empty($slug['stateNameSlug'])) {
                    $returnArray[$slug['stateName']] = $slug['stateNameSlug'];
                }
            }
        }
        return $returnArray;
    }

    public function getCityNameWithStateName()
    {
        return "{$this->name} {$this->stateName}";
    }

    public function getPhone()
    {
        return $this->hasOne(CityPhone::class, ['cityId' => 'id'])->inverseOf('city');
    }

    /**
     * @param $phone
     * @return CityPhone
     */
    public function newPhoneModel($phone)
    {
        $model = new CityPhone();
        $model->phone = $phone;
        $model->cityId = $this->id;
        return $model;
    }

    public function getStateName()
    {
        return $this->state->name;
    }

    public function getStateNameShort()
    {
        return $this->state->shortName;
    }

    /**
     * Get by slug
     * @param $slug
     * @return static|null
     */
    public static function findBySlug($slug, $stateNameShort = null)
    {
        $query = static::find()->andWhere(['slug' => $slug])->limit(1);
        if ($stateNameShort) {
            $query->andWhere(['stateNameShort' => $stateNameShort]);
        }

        return !empty($slug)
            ? $query->one()
            : null;
    }

    public function getState()
    {
        return $this->hasOne(\modules\account\models\ar\State::class, ['id' => 'stateId']);
    }

    public function getTimeZone($getFromApiIfEmpty = true, $save = true, $abbreviation = false)
    {
        $timeZone = null;
        if (!empty($this->timeZone)) {
            $timeZone = $this->timeZone;
        } elseif ($getFromApiIfEmpty) {
            $timeZone = \Yii::$app->googleTimeZone->getTimeZoneIdForCoordinates($this->latitude, $this->longitude);

            if (empty($timeZone)) {
                return null;
            }

            if ($save) {
                $this->timeZone = $timeZone;
                $this->save(false);
            }
        }

        if ($abbreviation) {
            return $this->getTimezoneAbbreviation($timeZone);
        }

        return $timeZone;
    }

    public function getTimezoneAbbreviation(string $timeZone)
    {
        try {
            $dt = new \DateTime('now', new \DateTimeZone($timeZone));
            return $dt->format('T');
        } catch (Exception $e) {
        }

        return null;
    }

    public static function getSeeAllTutorsQuery(string $stateSlug)
    {
        $citiesQuery = City::find()
            ->joinWith('zipcodes')
            ->andWhere(['stateNameSlug' => $stateSlug])
            ->select(Zipcode::tableName() . '.code');

        return Account::getSEOTutorsQuery()->andWhere(['zipCode' => $citiesQuery]);
    }
}
