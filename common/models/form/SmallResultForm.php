<?php

namespace common\models\form;

use common\components\HtmlPurifier;
use common\components\StringHelper;
use common\helpers\SmsHelper;
use common\models\City;
use common\models\Lead;
use common\models\Zipcode;
use common\components\ZipCodeHelper;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use modules\account\models\TutorSearch;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Json;

class SmallResultForm extends Model
{
    public $name;
    public $phone;
    public $email;
    public $description;
    public $subject;
    public $zipCode;
    public $distance;
    public $isSearchPage;
    public $gclid;
    public $backendType;
    public $siteType;
    public $isModal;

    /** @var SubjectOrCategory */
    protected $subjectModel;

    const ALWAYS_USE_BACKEND_TYPE = Lead::BACKEND_TYPE_SALESFORCE;

    const SOURCE_URL_SESSION_KEY = 'SmallResultPopUpSource';
    const FIELD_FOR_SOURCE_URL = 'LandingUrl';

    const GOOGLE_ADS_QUERY_PARAMETER = 'gclid';
    const BING_ADS_QUERY_PARAMETER = 'msclkid';
    const ADS_CHANNEL_COOKIE = 'heytutorAdsChanel';
    const ADS_CHANNEL_SEPARATOR = '---ht---';

    public function rules()
    {
        $distanceArray = array_keys(TutorSearch::getDistanceArray());

        return [
            [['name', 'phone', 'email', 'description', 'subject', 'zipCode', 'gclid', 'distance'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute);
            }
            ],
            [['name', 'phone', 'email', 'description', 'subject', 'zipCode', 'gclid'], 'string'],
            [['name', 'phone', 'email', 'description', 'isSearchPage'], 'required'],
            [['email'], 'email'],
            [['distance'], 'in', 'range' => $distanceArray],
            [['distance'], 'default', 'value' => $distanceArray[0]],
            [['isSearchPage', 'isModal'], 'boolean'],
            [['backendType'], 'in', 'range' => [Lead::BACKEND_TYPE_SALESFORCE]],
            [['siteType'], 'in', 'range' => [Lead::SITE_TYPE_MAIN]],
            [['zipCode'], 'exist', 'targetClass' => Zipcode::class, 'targetAttribute' => 'code'],
        ];
    }

    public function processData()
    {
        if (!$this->validate()) {
            return false;
        }

        if (empty($this->subject)) {
            $this->subject = '-';
        } else {
            $this->subjectModel = SubjectOrCategory::findById($this->subject);
            $this->subject = $this->subjectModel->getName() ?? '-';
        }

        if (empty($this->zipCode)) {
            $this->zipCode = ZipCodeHelper::getZipCodeByUserId();
        }

        try {
            $this->saveLead();
            $this->sendSms();
        } catch (\Throwable $exception) {
            $errorMessage = 'Error while processing form.';
            //write all Lead information
            Yii::error(
                "$errorMessage " . $exception->getMessage() . ' ' . $exception->getTraceAsString() . ' Lead Form: ' . Json::encode($this),
                'lead'
            );
            $this->addError('', $errorMessage);
            return false;
        }
        return true;
    }

    protected function sendSms()
    {
        //clear phone
        $phone = str_replace('(', '', $this->phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('-', '', $phone);

        $firstName = explode(' ', trim($this->name))[0];
        $cityName = Zipcode::find()
            ->andWhere(['code' => $this->zipCode])
            ->joinWith('city')->select(City::tableName() . '.name')->scalar();
        if (empty($firstName)) {
            Yii::error('FirstName for lead is empty. Lead:' . json_encode($this->attributes), 'leadSms');
            return;
        }
        if (empty($cityName)) {
            Yii::error('CityName for lead is empty. Lead:' . json_encode($this->attributes), 'leadSms');
            return;
        }

        $route = 'notification/send-lead-sms';
        $data = [
            'name' => $firstName,
            'phone' => $phone,
            'subject' => $this->subject,
            'cityName' => $cityName,
        ];
        $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
        Yii::$app->queue->post($task);
    }

    /**
     * save data about current url to session for display in email
     * @throws \yii\base\InvalidConfigException
     */
    public static function saveSourceToSession($data = null)
    {
        Yii::$app->session->set(self::SOURCE_URL_SESSION_KEY, $data ?? Yii::$app->request->getUrl());
    }

    /**
     * save data to cookie if user came from Google AdWords
     */
    public static function saveAdvertisingChannel()
    {
        $request = Yii::$app->request;
        $channel = null;
        if ($value = $request->get(static::GOOGLE_ADS_QUERY_PARAMETER)) {
            $channel = Lead::ADVERTISING_CHANNEL_GOOGLE_ADS;
        } elseif ($value = $request->get(static::BING_ADS_QUERY_PARAMETER)) {
            $channel = Lead::ADVERTISING_CHANNEL_BING_ADS;
        }

        if ($channel && $value) {
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => static::ADS_CHANNEL_COOKIE,
                'value' => $channel . static::ADS_CHANNEL_SEPARATOR . $value,
                //expiration time 1 day
                'expire' => time() + (24 * 60 * 60),
            ]));
        }
    }

    /**
     * @return array - Advertising Channel for Lead
     */
    public static function getAdvertisingChannel()
    {
        $value = Yii::$app->request->cookies->get(static::ADS_CHANNEL_COOKIE);
        if (
            !$value
            || false === strpos($value, static::ADS_CHANNEL_SEPARATOR)
        ) {
            return [
                'name' => Lead::ADVERTISING_CHANNEL_ORGANIC,
                'value' => null,
            ];
        }

        list($channel, $value) = explode(static::ADS_CHANNEL_SEPARATOR, $value);
        return [
            'name' => $channel,
            'value' => $value,
        ];
    }

    /**
     * @return string
     */
    public static function getSourceFromSession()
    {
        return Yii::$app->session->get(self::SOURCE_URL_SESSION_KEY, Yii::$app->request->url);
    }

    /**
     * generate HTML code for field that contains data about source landing url
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getLandingUrlHiddenHtml()
    {
        $fieldName = self::FIELD_FOR_SOURCE_URL;
        $fieldValue = Yii::$app->request->getUrl();
        return "<input type=\"hidden\" name=\"$fieldName\" value=\"$fieldValue\" />";
    }

    /**
     * @return string
     */
    public static function getIsSearchPageString()
    {
        return empty(Yii::$app->request->post(static::FIELD_FOR_SOURCE_URL)) ? 'true' : 'false';
    }

    protected function saveLead()
    {
        $advertisingChannel = static::getAdvertisingChannel();

        $lead = new Lead();
        $lead->firstName = $this->name;
        $lead->subject = $this->subject;
        $lead->subjectId = $this->subjectModel ? (int)$this->subjectModel->getId() : null;
        $lead->isCategory = $this->subjectModel ? (bool)$this->subjectModel->isCategory() : false;
        $lead->phone = SmsHelper::clear($this->phone);
        $lead->email = $this->email;
        $lead->description = $this->description;
        $lead->zipCode = $this->zipCode;
        $lead->distance = (int)$this->distance;
        $lead->source = $this->getSource();
        $lead->isSearchPage = (bool)$this->isSearchPage;
        $lead->advertisingChannel = $advertisingChannel['name'];
        $lead->clickId = $advertisingChannel['name'] === Lead::ADVERTISING_CHANNEL_GOOGLE_ADS
                ? $this->gclid
                : $advertisingChannel['value'];

        if (static::ALWAYS_USE_BACKEND_TYPE !== false) {
            $lead->backendType = static::ALWAYS_USE_BACKEND_TYPE;
        } else {
            $lead->backendType = $this->backendType;
        }
        $lead->siteType = $this->siteType;
        if (!$lead->save()) {
            if ($lead->hasErrors()) {
                foreach ($lead->getErrors() as $errorArray) {
                    foreach ($errorArray as $error) {
                        $this->addError('', $error);
                    }
                }
            } else {
                $this->addError('', 'Unknown error');
            }

            throw new Exception('Data validation error');
        }
        return true;
    }

    protected function getSource()
    {
        if ($this->isModal) {
            $source = static::getSourceFromReferer();
            if (!empty($source)) {
                return $source;
            }
        }

        return static::getSourceFromSession();
    }

    public static function getSourceFromReferer(): string
    {
        $request = Yii::$app->getRequest();
        $referrer = $request->referrer;
        $hostInfo = rtrim($request->getHostInfo(), '/');
        if (
            $referrer
            && StringHelper::startsWith($referrer, $hostInfo)
        ) {
            return substr($referrer, strlen($hostInfo));
        }
        return '';
    }
}
