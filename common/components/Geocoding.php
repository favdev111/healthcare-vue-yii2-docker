<?php

namespace common\components;

use common\models\GooglePlace;
use yii\base\Component;
use yii\helpers\VarDumper;
use yii\httpclient\Client;

class Geocoding extends Component
{
    public $key = '';
    public $components = ['country:US'];

    protected $city;
    protected $state;
    protected $latitude;
    protected $longitude;

    public function getGeoData($address)
    {
        if (! is_array($this->components)) {
            $this->components = (array)$this->components;
        }

        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('get')
            ->setUrl('https://maps.googleapis.com/maps/api/geocode/json')
            ->setData([
                'address' => $address,
                'language' => 'en',
                'components' => implode('|', $this->components),
                'key' => env('GOOGLE_MAPS_API_KEY'),
            ]);

        $response = $request->send();
        if (! $response->isOk) {
            return false;
        }

        $data = json_decode($response->getContent());
        if ($data && count($data->results)) {
            return $data->results;
        }

        return false;
    }

    public function getPlaceDetails($placeId)
    {
        if (! is_array($this->components)) {
            $this->components = (array)$this->components;
        }

        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('get')
            ->setUrl('https://maps.googleapis.com/maps/api/place/details/json')
            ->setData([
                'placeid' => $placeId,
                'language' => 'en',
//                'region' => 'US',
                'key' => env('GOOGLE_MAPS_API_KEY'),
            ]);

        $response = $request->send();

        if (! $response->isOk) {
            return false;
        }

        $data = $response->getData();
        if ($data && $data['status'] === 'OK' && ! empty($data['result'])) {
            return $data['result'];
        }

        return false;
    }

    /**
     * @param $placeId
     * @return bool|\StdClass
     */
    public function getZipCodeByPlaceId($placeId)
    {
        $model = GooglePlace::find()->andWhere(['placeId' => $placeId])->limit(1)->one();

        if ($model) {
            $json = $model->data;
        } else {
            $json = $this->getPlaceDetails($placeId);
            if (false === $json) {
                return false;
            }
        }

        $result = new \StdClass();
        if (
            in_array('street_address', $json['types'])
            || in_array('premise', $json['types'])
            || in_array('subpremise', $json['types'])
        ) {
            foreach ($json['address_components'] as $component) {
                $_types = $component['types'];

                if (in_array('country', $_types)) {
                    $result->country = $component['short_name'];
                }

                if (in_array('postal_code', $_types)) {
                    $result->zipCode = $component['long_name'];
                }

                if (in_array('administrative_area_level_1', $_types)) {
                    $result->state = $component['short_name'];
                }

                if (in_array('locality', $_types)) {
                    $result->city = $component['short_name'];
                }
            }
        } else {
            return false;
        }

        if (empty($result->zipCode)) {
            return false;
        }

        if (! isset($json['geometry']['location'])) {
            return false;
        }

        if (
            ! isset($result->country)
            || (strtoupper($result->country) !== 'US')
        ) {
            return false;
        }

        $result->address = $json['formatted_address'];
        if (StringHelper::endsWith($result->address, $result->zipCode)) {
            $result->address = substr($result->address, 0, strlen($result->address) - strlen($result->zipCode));
            $result->address = trim($result->address);
        }

        $result->latitude = (double)$json['geometry']['location']['lat'];
        $result->longitude = (double)$json['geometry']['location']['lng'];

        if (! $model) {
            $model = new GooglePlace();
            $model->placeId = $placeId;
            $model->data = $json;
            $model->save();
        }

        $result->googlePlaceId = $model->id;

        return $result;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }
}
