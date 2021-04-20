<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Html;
use yii\httpclient\Client;

class Shareasale extends Component
{
    /**
     * @var $merchantId string ShareASale merchant ID
     */
    public $merchantId;

    /**
     * @var $token string ShareASale Token
     */
    public $token;

    /**
     * @var $secret string ShareASale Secret
     */
    public $secret;

    /**
     * @var $version string ShareASale Version
     */
    public $version = '2.8';

    public $leadPixelUrl = 'https://shareasale.com/sale.cfm';

    public $apiUrl = 'https://api.shareasale.com/w.cfm';

    const SESSION_KEY = 'shareASaleTrackingNumber';

    public function init()
    {
        parent::init();

        if (!$this->merchantId) {
            throw new Exception('Merchant ID is required for ShareASale to work properly');
        }
    }

    /**
     * @return null|string <img> tag for ShareASale lead pixel in case trackingNumber was set earlier
     */
    public function getLeadPixel()
    {
        $trackingNumber = $this->getTrackingNumber(true);
        if (!$trackingNumber) {
            return null;
        }

        $params = [
            'amount' => '0.00',
            'tracking' => $trackingNumber,
            'transtype' => 'lead',
            'merchantID' => $this->merchantId,
        ];
        $url = $this->leadPixelUrl . '?' . http_build_query($params);
        return Html::img($url, ['width' => 1, 'height' => 1]);
    }

    /**
     * Set Tracking Number after Student Signup to show lead pixel (using getLeadPixel) for ShareASale to track signup
     *
     * @param $studentId integer Newly registered student ID
     */
    public function setTrackingNumber($studentId)
    {
        // Using session since currently signup is made via AJAX. Need to show lead pixel later
        Yii::$app->session->set(self::SESSION_KEY, $studentId);
    }

    /**
     * Get Tracking Number from session
     *
     * @param $removeFromSession boolean Whether to remove from session or not (false by default)
     *
     * @return integer Tracking Number (Student Id)
     */
    public function getTrackingNumber($removeFromSession = false)
    {
        // Using session since currently signup is made via AJAX. Need to show lead pixel later
        $trackingNumber = Yii::$app->session->get(self::SESSION_KEY);
        if ($removeFromSession) {
            Yii::$app->session->remove(self::SESSION_KEY);
        }
        return $trackingNumber;
    }

    /**
     * Send Reference request to ShareASale to convert lead to sale
     *
     * @param $studentId integer Student ID to convert lead to sale for
     * @param $studentCreatedAt string Date when student was created
     * @param $amount double Amount of the order
     * @param $transactionId integer Paid transaction ID
     * @return bool
     */
    public function sendReference($studentId, $studentCreatedAt, $amount, $transactionId)
    {
        $timestamp = gmdate(DATE_RFC1123);

        $action = 'reference';

        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('get')
            ->addHeaders([
                'x-ShareASale-Date' => $timestamp,
                'x-ShareASale-Authentication' => $this->generateHash($action, $timestamp),
            ])
            ->setUrl($this->apiUrl)
            ->setData([
                'merchantId' => $this->merchantId,
                'token' => $this->token,
                'version' => $this->version,
                'action' => $action,
                'date' => Yii::$app->formatter->asDate($studentCreatedAt, 'php:n/j/Y'),
                'ordernumber' => $studentId,
                'transtype' => 'sale',
                'amount' => $amount,
                'tracking' => $transactionId,
            ]);

        $response = $request->send();
        $responseContent = $response->getContent();
        if (!$response->isOk || stripos($responseContent, "Error Code ")) {
            Yii::error('Failed to send ShareASale reference. Details: ' . $responseContent, 'shareasale');
            return false;
        }

        Yii::info('ShareASale response details: ' . $responseContent, 'shareasale');
        return true;
    }

    /**
     * Generate Hash to send via Request Headers
     * String to hash YourAPIToken:CurrentDateInUTCFormat:APIActionValue:YourAPISecret
     *
     * @param $actionVerb string Action which will be executed
     * @param $timestamp string Timestamp in RFC1123
     *
     * @return string Hashed string
     */
    protected function generateHash($actionVerb, $timestamp)
    {
        $stringToHash = $this->token . ':' . $timestamp . ':' . $actionVerb . ':' . $this->secret;

        return hash("sha256", $stringToHash);
    }
}
