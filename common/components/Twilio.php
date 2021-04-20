<?php

/**
 * @author Bryan Tan <bryantan16@gmail.com>
 */

namespace common\components;

use Twilio\Rest\Lookups\V1\PhoneNumberInstance;
use yii\base\Component;
use yii\base\InvalidConfigException;
use Twilio\Rest\Client;

class Twilio extends Component
{
    const US_COUNTRY_CODE = '+1';
    public $sid;
    public $token;
    private $_client = null;
    public function init()
    {
        if (!$this->sid) {
            throw new InvalidConfigException('SID is required');
        }
        if (!$this->token) {
            throw new InvalidConfigException('Token is required');
        }
    }
    public function getClient()
    {
        if ($this->_client === null) {
            $client = (new Client($this->sid, $this->token));
            $this->_client = $client;
        }
        return $this->_client;
    }

    /**
     * @param string $number
     * @return \Twilio\Rest\Lookups\V1\PhoneNumberInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getPhoneInfo(string $number): PhoneNumberInstance
    {
        $number  = static::US_COUNTRY_CODE . $number;
        return $this->getClient()->lookups->v1->phoneNumbers($number)->fetch(["type" => "carrier"]);
    }
}
