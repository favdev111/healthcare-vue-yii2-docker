<?php

namespace common\components\api\services;

use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 * Class NPIClient
 * @package common\components\api\services
 */
class NPIClient extends Client
{
    /**
     * @var string
     */
    public $baseUrl = 'https://npiregistry.cms.hhs.gov/api/';
    /**
     * @var string Version of API
     */
    public $defaultVersion = '2.1';

    /**
     * @param array|string $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return void|\yii\httpclient\Request
     * @throws NotSupportedException
     */
    public function post($url, $data = null, $headers = [], $options = [])
    {
        throw new NotSupportedException(get_class($this) . ' does not support post().');
    }

    /**
     * @param array|string $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return void|\yii\httpclient\Request
     * @throws NotSupportedException
     */
    public function put($url, $data = null, $headers = [], $options = [])
    {
        throw new NotSupportedException(get_class($this) . ' does not support put().');
    }

    /**
     * @param array|string $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return void|\yii\httpclient\Request
     * @throws NotSupportedException
     */
    public function patch($url, $data = null, $headers = [], $options = [])
    {
        throw new NotSupportedException(get_class($this) . ' does not support patch().');
    }

    /**
     * @param array|string $url
     * @param null $data
     * @param array $headers
     * @param array $options
     * @return void|\yii\httpclient\Request
     * @throws NotSupportedException
     */
    public function delete($url, $data = null, $headers = [], $options = [])
    {
        throw new NotSupportedException(get_class($this) . ' does not support delete().');
    }

    /**
     * @param array|string $url
     * @param array $headers
     * @param array $options
     * @return void|\yii\httpclient\Request
     * @throws NotSupportedException
     */
    public function head($url, $headers = [], $options = [])
    {
        throw new NotSupportedException(get_class($this) . ' does not support head().');
    }

    /**
     * @param array|string $url
     * @param array $options
     * @return void|\yii\httpclient\Request
     * @throws NotSupportedException
     */
    public function options($url, $options = [])
    {
        throw new NotSupportedException(get_class($this) . ' does not support options().');
    }

    /**
     * @param array|string $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return \yii\httpclient\Request
     */
    public function get(
        $url,
        $data = [],
        $headers = [],
        $options = []
    ) {
        if (!isset($data['version'])) {
            $data['version'] = $this->defaultVersion;
        }
        return parent::get($url, $data, $headers, $options);
    }

    /**
     * @param $number
     * @param $data
     * @return mixed
     */
    public function getByNumber(int $number, array $data = [])
    {
        ArrayHelper::setValue($data, 'number', $number);
        return $this->get($data)->send()->data;
    }
}
