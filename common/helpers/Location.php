<?php

namespace common\helpers;

use common\models\City;
use common\models\Zipcode;
use modules\account\models\ar\State;
use yii\helpers\Inflector;

class Location
{
    /**
     * @param      $cityName
     * @param      $stateName
     * @param      $stateShortName
     * @param null $zipcode
     * @param null $zipcodeLatitude
     * @param null $zipcodeLongitude
     *
     * @return bool|int Return city ID if success else FALSE
     */

    public static function addLocation(
        $cityName,
        $stateName,
        $stateShortName,
        $zipcode = null,
        $zipcodeLatitude = null,
        $zipcodeLongitude = null,
        $update = false
    ) {
        $modelState = State::find()->andWhere([
            'name' => $stateName,
            'shortName' => $stateShortName,
        ])->limit(1)
            ->one();

        if (empty($modelState)) {
            return false;
        }

        $modelCity = City::findOne([
            'name' => $cityName,
            'stateId' => $modelState->id,
        ]);

        if (!$modelCity) {
            $modelCity = new City();
            $modelCity->setAttributes(
                [
                    'name' => $cityName,
                    'stateId' => $modelState->id,
                ],
                false
            );

            if (!$modelCity->save(false)) {
                return false;
            }
        }

        $cityId = $modelCity->id;

        if (!empty($zipcode)) {
            $modelZipcode = Zipcode::findOne([
                'code' => $zipcode,
            ]);

            if (!$modelZipcode) {
                $modelZipcode = new Zipcode();
                $modelZipcode->setAttributes(
                    [
                        'code' => $zipcode,
                        'cityId' => $cityId,
                        'latitude' => $zipcodeLatitude,
                        'longitude' => $zipcodeLongitude,
                    ],
                    false
                );
                $modelZipcode->save(false);
            } elseif (empty($modelZipcode->cityId) || $update) {
                $modelZipcode->setAttributes(
                    [
                        'cityId' => $cityId,
                        'latitude' => $zipcodeLatitude,
                        'longitude' => $zipcodeLongitude,
                    ],
                    false
                );
                $modelZipcode->save(false);
            }
        }

        return $cityId;
    }

    public static function getModelCity()
    {
        return new City();
    }

    public static function getModelZipCode()
    {
        return new Zipcode();
    }

    public static function getCityId($cityName)
    {
        $modelCity = City::findOne([
            'name' => $cityName,
        ]);

        return $modelCity ? $modelCity->id : false;
    }

    public static function getCitySlug($cityName)
    {
        return strtolower(Inflector::slug($cityName, '-', true));
    }

    public static function getCityFromSlug($slug)
    {
        return strtolower(str_replace('-', ' ', $slug));
    }

    public static function getZipcodeLocation($code)
    {
        $modelZipcode = Zipcode::findOne(['code' => trim((string)$code)]);
        if (!$modelZipcode) {
            return false;
        }

        return [
            'latitude' => $modelZipcode->latitude,
            'longitude' => $modelZipcode->longitude,
        ];
    }

    public static function addZipcode($code, $latitude, $longitude)
    {
        $modelZipcode = Zipcode::findOne([
            'code' => $code,
        ]);

        if ($modelZipcode) {
            return true;
        }

        $modelZipcode = new Zipcode();
        $modelZipcode->setAttributes(
            [
                'code' => $code,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            false
        );

        return $modelZipcode->save(false);
    }

    public static function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $lat1 = deg2rad($latitude1);
        $lng1 = deg2rad($longitude1);
        $lat2 = deg2rad($latitude2);
        $lng2 = deg2rad($longitude2);
        // calculate great-circle distance
        $distance = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lng1 - $lng2));
        // distance in given format
        // Checking for NaN. E.g. coordinates are the same and formula returns NaN
        $miles = (3959 * (!is_nan($distance) ? $distance : 0));

        return round($miles, 2);
    }
}
