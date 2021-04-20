<?php

namespace common\components;

use common\models\Lead;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\caching\CacheInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\Response;

class SalesforceLeadService extends Component
{
    /* Auth params */
    public $url;
    public $clientId;
    public $clientSecret;
    public $username;
    public $password;
    public $securityToken;
    public $webhookSecurityToken;

    /**
     * @var CacheInterface|string the cache object or the application component ID of the cache object.
     */
    public $cache = 'cache';

    /**
     * @var string the cache key for cached token
     */
    protected $cacheKey = __CLASS__;

    /** @var Client */
    private $httpClient;

    private $httpClientOptions = [
        \CURLOPT_SSLVERSION => \CURL_SSLVERSION_TLSv1_2,
    ];

    public function init()
    {
        parent::init();

        if (
            !$this->clientId
            || !$this->url
            || !$this->clientSecret
            || !$this->username
            || !$this->password
            || !$this->securityToken
        ) {
            throw new InvalidConfigException('url, clientId, clientSecret, username, password, securityToken must be set');
        }

        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache, false);
        }

        $this->httpClient = new Client([
            'transport' => 'yii\httpclient\CurlTransport',
            'formatters' => [
                Client::FORMAT_JSON => [
                    'class' => 'yii\httpclient\JsonFormatter',
                    'encodeOptions' => \JSON_FORCE_OBJECT,
                ],
            ],
        ]);
    }

    /**
     * @param bool $renew
     * @return array
     */
    protected function getLogin(bool $renew = false)
    {
        $data = $this->cache->get($this->cacheKey);
        if (!$renew && $data) {
            return $data;
        }

        /**
         * @var Response $response
         */
        $response = $this->createHttpClientRequest()
            ->setMethod('post')
            ->setUrl(rtrim($this->url, '/') . '/services/oauth2/token')
            ->addHeaders([
                'content-type' => 'application/json',
            ])
            ->setData([
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $this->username,
                'password' => $this->password . $this->securityToken,
            ])
            ->send();

        if ($response->isOk) {
            $this->cache->set($this->cacheKey, $response->data, 7 * 60 * 60);
            return $response->data;
        }

        Yii::error('Status ' . $response->getStatusCode() . 'Can not retrieve token', 'lead');
        throw new InvalidValueException('Can not retrieve token: ' . $response->content);
    }

    /**
     * @return \yii\httpclient\Request
     */
    protected function createHttpClientRequest()
    {
        return $this->httpClient
            ->createRequest()
            ->setOptions($this->httpClientOptions);
    }

    protected function getToken(bool $renew = false)
    {
        $data = $this->getLogin($renew);
        return $data['access_token'];
    }

    protected function getInstanceUrl()
    {
        $data = $this->getLogin();
        return $data['instance_url'];
    }

    public function sendLead(Lead $model)
    {
        $names = explode(' ', trim($model->name), 2);
        $firstName = $names[0] ?? '-';
        $lastName = $names[1] ?? '-';

        $data = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'company' => 'What\'s in it, Clinic.',
            'email' => $model->email,
            'description' => $model->description,
            'phone' => $model->phoneNumber,
            'Symptoms__c' => implode(', ', ArrayHelper::getColumn($model->data->relations ?? [], 'name')),
        ];

        Yii::info(
            'LeadId: ' . $model->id . '; Request Data: ' . Json::encode($data),
            'lead'
        );

        // Retry if false
        for ($i = 1; $i <= 2; $i++) {
            /**
             * @var Response $response
             */
            $response = $this->createLeadRequest($data, ($i === 2))->send();
            if ($response->isOk) {
                return $response['id'];
            }

            Yii::error(
                'Response: ' . $response->content,
                'lead'
            );
        }

        return null;
    }

    protected function createLeadRequest($data, $renewToken = false)
    {
        $token = $this->getToken($renewToken);
        return $this->createHttpClientRequest()
            ->setMethod('post')
            ->setUrl($this->getInstanceUrl() . '/services/data/v44.0/sobjects/Lead')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'content-type' => 'application/json',
                'authorization' => 'Bearer ' . $token,
            ])
            ->setData($data);
    }

    public function createUrlToLead(string $sfLeadId): string
    {
        return $this->getInstanceUrl() . '/lightning/r/Lead/' . $sfLeadId . '/view';
    }


    public function updateLeadRequest(string $url, $data, $renewToken = false)
    {
        $token = $this->getToken($renewToken);
        return $this->createHttpClientRequest()
            ->setMethod('patch')
            ->setUrl($this->getInstanceUrl() . $url)
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'content-type' => 'application/json',
                'authorization' => 'Bearer ' . $token,
            ])
            ->setData($data);
    }
}
