<?php

namespace common\components;

use Spatie\GoogleTimeZone\GoogleTimeZone;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class GoogleTimeZoneComponent
 * @package app\components
 */
class GoogleTimeZoneComponent extends Component
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $language = 'en';

    /**
     * @var GoogleTimeZone
     */
    private $client;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->key) {
            throw new InvalidConfigException("GoogleTimeZoneComponent's key is not set.");
        }

        $this->client = new GoogleTimeZone();
        $this->client->setApiKey($this->key);
        $this->client->setLanguage($this->language);

        parent::init();
    }


    /**
     * @param float $lat
     * @param float $lng
     * @return null|string
     */
    public function getTimeZoneIdForCoordinates(float $lat, float $lng): ?string
    {
        try {
            $result = $this->client->getTimeZoneForCoordinates($lat, $lng);
            if (empty($result) || !isset($result['timeZoneId'])) {
                return null;
            }

            return $result['timeZoneId'];
        } catch (\Exception $e) {
            \Yii::error(
                'Failed to receive timezone by location in GoogleTimeZoneComponent. Error: ' . $e->getMessage()
            );
        }

        return null;
    }
}
