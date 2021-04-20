<?php

namespace modules\payment\components;

use common\components\Formatter;
use common\helpers\Url;
use modules\account\models\ClientBalanceTransaction;
use modules\account\models\forms\PaymentInfo;
use modules\account\models\Lesson;
use modules\account\models\PaymentAccount;
use modules\payment\components\interfaces\PaymentInterface;
use modules\payment\components\parts\Ach;
use modules\payment\models\CardInfo;
use modules\payment\models\PaymentBankAccount;
use modules\payment\models\PaymentCustomer;
use modules\account\models\Account as UserAccount;
use modules\payment\models\PlatformPayout;
use modules\payment\models\Transaction;
use modules\payment\Module;
use Stripe\Account;
use Stripe\Balance;
use Stripe\BankAccount;
use Stripe\Card;
use Stripe\Customer;
use Stripe\Error\InvalidRequest;
use Stripe\Event;
use Stripe\Payout;
use Stripe\Person;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Transfer;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

/**
 * Yii stripe component.
 *
 * @property Ach $ach
 */
class Payment extends \yii\base\Component implements PaymentInterface
{
    const USD_CURRENCY = 'usd';
    const CAPABILITY_TRANSFER = 'transfers';
    const CAPABILITY_CARD = 'card_payments';
    const CAPABILITY_LEGACY = 'legacy_payments';
    const BUSINESS_TYPE_COMPANY = 'company';
    const BUSINESS_TYPE_INDIVIDUAL = 'individual';

    const PATIENT_MAX_CARD_COUNT = 10;
    public static $defaultCapabilities = [self::CAPABILITY_TRANSFER];

    /**
     * @var Ach|null Local Ach class item. Use getAch() or ->ach
     */
    protected $achEntity;

    /**
     * @param bool $refresh Whether to create new Ach class item or use existing
     * @return Ach|null
     */
    public function getAch($refresh = false)
    {
        if ($refresh || !$this->achEntity) {
            $this->achEntity = new Ach([
                'paymentComponent' => $this,
            ]);
        }
        return $this->achEntity;
    }

    // Stripe uses prices in cents. We use in dollars.
    const FUNDS_FACTOR = 100;

    // Stripe Verified status text constant
    const STRIPE_ACCOUNT_VERIFIED = 'verified';

    // Stripe Individual bank account type
    const STRIPE_ACCOUNT_TYPE_INDIVIDUAL = 'individual';
    // Stripe Company bank account type
    const STRIPE_ACCOUNT_TYPE_COMPANY = 'company';

    /**
     * @var Lesson $refundLesson - storage for lesson in process of partial refund of group transaction
     */
    public $refundLesson;


    /**
     * @see Stripe
     * @var string Stripe's public key
     */
    public $publicKey;

    /**
     * @see Stripe
     * @var string Stripe's private key
     */
    public $privateKey;

    /**
     * @see Stripe
     * @var string Stripe's API version
     */
    public $apiVersion;

    /**
     * @see Stripe
     * @var string Stripe's Webhook Secret
     */
    public $webhookSecret;

    public $pathPaymentTypeImg = '/img/payment/';

    protected $paymentImages = [
        'visa'               => '1.png',
        'mastercard'         => '2.png',
        'maestro'            => '3.png',
        'dinersclub'         => '10.png',
        'diners club'        => '10.png',
        'discover'           => '14.png',
        'unionpay'           => '25.png',
        'jcb'                => '16.png',
        'amex'               => '22.png',
        'american express'   => '22.png',
        'forbrugsforeningen' => '26.png',
        'elo'                => '27.png',
        'dankort'            => '28.png',
    ];

    /**
     * Convert Dollars amount to Cents one required for stripe processing
     *
     * @param $amount double
     * @return integer
     */
    public static function toStripeAmount($amount)
    {
        return $amount * self::FUNDS_FACTOR;
    }

    /**
     * Convert Cents amount from Stripe to Dollars one for internal processing
     *
     * @param $amount integer
     * @return float|int
     */
    public static function fromStripeAmount($amount)
    {
        return $amount / self::FUNDS_FACTOR;
    }

    /**
     * @return array
     */
    public function getImagesArray()
    {
        $typeImg = [];
        foreach ($this->paymentImages as $type => $img) {
            $typeImg[$type] = $this->getPaymentImgPath($type);
        }

        return $typeImg;
    }

    /**
     * @param $type string CC processor name (e.g. visa)
     * @return string path to CC processor image
     * @throws \yii\base\InvalidConfigException
     */
    public function getPaymentImgPath($type)
    {
        if (!isset($this->paymentImages[$type])) {
            return '';
        }

        $img = $this->paymentImages[$type];

        return Yii::$app->view->theme->getUrl(
            $this->pathPaymentTypeImg . $img
        );
    }

    /**
     * @param $amount
     * @param $account
     * @param $sourceType
     * @return Payout
     */
    public function transferToBank($amount, $sourceType, $account = null)
    {
        $accountArray = $account ? ["stripe_account" => $account] : null;
        return Payout::create(
            [
                "amount" => $amount,
                "currency" => static::USD_CURRENCY,
                "source_type" => $sourceType,
            ],
            $accountArray
        );
    }

    public function lessonTransferToTutor($amount, $tutorStripeAccount, $sourceTransactionExternalId = null)
    {
        $params = [
            "amount" => $amount,
            "currency" => static::USD_CURRENCY,
            "destination" => $tutorStripeAccount,
        ];

        if (!empty($sourceTransactionExternalId)) {
            $params["source_transaction"] = $sourceTransactionExternalId;
        } else {
            $params['source_type'] = PlatformPayout::SOURCE_CARD_LABEL;
        }

        try {
            //try to create transfer from card balance
            $transferObj = Transfer::create($params);
        } catch (\Throwable $exception) {
            if (empty($sourceTransactionExternalId)) {
                //in case of error retry with BA balance
                $params['source_type'] = PlatformPayout::SOURCE_BA_LABEL;
                $transferObj = Transfer::create($params);
            } else {
                throw $exception;
            }
        }
        return $transferObj;
    }

    /**
     * @param $bankToken string Stripe Bank Token
     * @return \modules\payment\models\Account
     * @throws \Exception
     */
    public function createBankAccount($bankToken)
    {
        $acc = $this->getIndentity();
        $stripeAccount = $this->getStripeAccount();
        $paymentAccount = $acc->paymentAccount;

        /**
         * @var $paymentModule Module
         */
        $paymentModule = Yii::$app->getModule('payment');
        /**
         * @var $bankAccount \modules\payment\models\Account
         */
        $bankAccount = $paymentModule->model('BankAccount');

        try {
            $bank = $stripeAccount->external_accounts->create(array(
                "external_account" => $bankToken,
            ));
        } catch (\Exception $e) {
            Yii::error('Failed to create payment account. Exception: ' . $e->getTraceAsString(), 'payment');
            $bankAccount->addError('', 'Failed to create payment account. ' . $e->getMessage() . ' Please try again or contact us.');
            return $bankAccount;
        }


        $bankAccount->setAttributes([
            'paymentBankId' => $bank->id,
            'paymentAccountId' => $paymentAccount->id,
            'active' => $paymentAccount->getBankAccounts()->exists() ? 0 : 1,
        ]);
        $bankAccount->save();

        return $bankAccount;
    }

    /**
     * Updating default bank account
     * @param $bankAccountId
     * @param $account
     * @return \Stripe\ExternalAccount
     * @throws \Exception
     */
    public function updateDefaultBankAccount($bankAccountId, $account = null)
    {
        $bankAccount = $this->getBankAccount($bankAccountId, $account);
        $bankAccount->default_for_currency = true;
        $bankAccount->save();
        return $bankAccount->save();
    }

    public function createAccount($accountId, &$error = null)
    {
        $account = $accountId ? \modules\account\models\Account::findOne($accountId) : null;
        /**
         * @var $account \modules\account\models\Account
         */
        if (!$account) {
            Yii::error('No app account id provided', 'payment');
            $error = 'Failed to create payment account. Please try again or contact us.';
            return null;
        }

        try {
            $stripeAccount = $this->createCustomer($account, $account->isTutor());
        } catch (\Exception $e) {
            Yii::error('Failed to create stripe account. ' . json_encode($e->getTrace()) . "  " . $e->getMessage(), 'payment');
            $error = $e->getMessage();
            return null;
        }

        /**
         * @var $model \modules\payment\models\Account
         */
        $model = Yii::$app->getModule('payment')->model('Account');
        $model->accountId = $accountId;
        $model->paymentAccountId = $stripeAccount->id;
        $model->capabilities = $stripeAccount->capabilities->toArray();
        $model->updatesRequired = !$model->checkCapabilities();
        $model->save();
        return $model;
    }

    public function createConnectOnboadringLink(string $accountId, $isB2b = false): string
    {
        if ($isB2b) {
            $success = Url::toB2bRoute('/payment-settings', ['stripeUpdateStatus' => 'success']);
            $failed = Url::toB2bRoute('/payment-settings', ['stripeUpdateStatus' => 'failed']);
        } else {
            $failed = (Yii::$app->request->isSecureConnection ? 'https' : 'http') . '://' . Yii::$app->request->hostName . '/failed-update-data';
            $success = (Yii::$app->request->isSecureConnection ? 'https' : 'http') . '://' . Yii::$app->request->hostName . '/profile/payment-info';
        }
        return \Stripe\AccountLink::create([
            'account' => $accountId,
            'failure_url' => $failed,
            'success_url' => $success,
            'type' => 'custom_account_verification',
        ])->url ?? '';
    }
    public function updateCompanyContactName($account, $firstName, $lastName)
    {
        $stripeAccount = Yii::$app->payment->getStripeAccount($account);
        $stripeAccount->legal_entity->first_name = $firstName;
        $stripeAccount->legal_entity->last_name = $lastName;
        try {
            return $stripeAccount->save();
        } catch (\Exception $e) {
            Yii::error('Failed to update payment account. ' . $e->getMessage() . '  Exception: ' . $e->getTraceAsString(), 'payment');
            return false;
        }
    }

    public function updateEmail($account)
    {
        $stripeAccount = Yii::$app->payment->getStripeAccount($account);
        $stripeAccount->email = $account->email;
        try {
            return $stripeAccount->save();
        } catch (\Exception $e) {
            Yii::error('Failed to update payment account. ' . $e->getMessage() . '  Exception: ' . $e->getTraceAsString(), 'payment');
            return false;
        }
    }

    protected function findPlace(string $placeId): array
    {
        $client = new \GooglePlaces\Client(env('GOOGLE_MAPS_API_KEY'));
        $result = [];
        try {
            $response = $client->placeDetails($placeId)->request();
            if (!empty($response['result']['address_components'])) {
                foreach ($response['result']['address_components'] as $address_component) {
                    if (empty($address_component['types'])) {
                        continue;
                    }

                    if (in_array('street_number', $address_component['types'])) {
                        $result['number'] = $address_component['long_name'];
                    }

                    if (in_array('route', $address_component['types'])) {
                        $result['street'] = $address_component['long_name'];
                    }

                    if (in_array('route', $address_component['types'])) {
                        $result['city'] = $address_component['long_name'];
                    }

                    if (in_array('administrative_area_level_1', $address_component['types'])) {
                        $result['state'] = $address_component['short_name'];
                    }

                    if (in_array('postal_code', $address_component['types'])) {
                        $result['zip'] = $address_component['long_name'];
                    }
                }
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage() . "\nPlace Id = $placeId", 'payment');
        }
        return $result;
    }

    public function fillStripeAccountWithData($accountId, PaymentInfo $paymentInfo, &$error = null)
    {
        try {
            /**
             * @var $account UserAccount
             */
            $account = UserAccount::find()->tutorOrCompany()->andWhere(['id' => $accountId])->one();
            if ($this->isVerified()) {
                return true;
            }

            $stripeAccount = $this->getStripeAccount($account);
            if ($account->isTutor()) {
                $stripeAccount->business_type = static::BUSINESS_TYPE_INDIVIDUAL;
            }

            $profile = $account->profile;
            $ip = $account->createdIp;
            $dob = explode('-', $profile->dateOfBirth);
            /**
             * @var Person $person
             */
            if (!empty($stripeAccount->persons()->data[0]->id)) {
                $person = Account::retrievePerson(
                    $stripeAccount->id,
                    $stripeAccount->persons()->data[0]->id ?? ''
                );
            } else {
                $person = Account::createPerson($stripeAccount->id);
            }

            if (empty($person)) {
                Yii::error('Person not found for account ' . $person, 'payment');
                return false;
            }

            if (!empty($paymentInfo->placeId)) {
                $addrInfo = $this->findPlace($paymentInfo->placeId);
                $person->address->city = $addrInfo['city'] ?? '';
                $person->address->state = $addrInfo['state'] ?? '';
                $person->address->postal_code = $addrInfo['zip'] ?? '';
                $person->address->line1 = ($addrInfo['number'] ?? '') . ' ' . ($addrInfo['street'] ?? '');
            }

            $person->dob->day = $dob[2];
            $person->dob->month = $dob[1];
            $person->dob->year = $dob[0];
            $person->first_name = $profile->firstName;
            $person->last_name = $profile->lastName;
            if ($account->isCrmAdmin()) {
                $stripeAccount->company->name = $profile->companyName;
                $stripeAccount->company->tax_id = $profile->taxId;
            }

            if (!$person->ssn_last_4_provided && !empty($paymentInfo->ssn)) {
                // Fill SSN for non verified accounts only
                $person->ssn_last_4 = substr($paymentInfo->ssn, -4);
            }

            if (!$person->save()) {
                Yii::error('Failed to update person for account ' . $stripeAccount->id, 'payment');
                return false;
            }
            $stripeAccount->tos_acceptance->date = time();
            $stripeAccount->tos_acceptance->ip = $ip;
            return $stripeAccount->save();
        } catch (\Exception $e) {
            Yii::error('Failed to create payment account. Exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'payment');
            $error = $e->getMessage();
            return false;
        }
    }

    public function attachCardToCustomer($cardToken, $account, $setActive = true)
    {
        $paymentCustomerModel = PaymentCustomer::findOne(['accountId' => $account->id]);

        if (empty($paymentCustomerModel->customerId)) {
            $paymentCustomerModel = $this->createPaymentCustomer($account, $paymentCustomerModel);
        }

        $stripeCustomer = Customer::retrieve($paymentCustomerModel->customerId);
        $card = $stripeCustomer->sources->create(['source' => $cardToken]);

        /**
         * @var $paymentModule Module
         */
        $paymentModule = Yii::$app->getModule('payment');
        /**
         * @var $cardInfo CardInfo
         */
        $cardInfo = $paymentModule->model('CardInfo');
        $cardInfo->month = $card->exp_month;
        $cardInfo->year = $card->exp_year;
        $cardInfo->cardNumber = $card->last4;
        $cardInfo->brand = strtolower($card->brand);
        $cardInfo->cardId = $card->id;
        $cardInfo->tokenCard = $cardToken;
        $cardInfo->holderName = $card->name;
        $cardInfo->stripeCustomerId = $paymentCustomerModel->id;
        // Set Card to active only if no credit card or bank account exist
        $cardInfo->active = $setActive || !$paymentCustomerModel->activeCardOrBankAccount;
        $cardInfo->save();
        return $cardInfo;
    }

    public function retrievePlatformBalanceObject()
    {
        Stripe::setApiKey(\Yii::$app->payment->privateKey);
        return Balance::retrieve();
    }

    public function createStripeCustomer($name)
    {
        return Customer::create(array(
            "description" => "Customer for HeyTutor - " . $name,
        ));
    }

    /**
     * @param $account
     * @param null|PaymentCustomer $paymentCustomerModel
     * @return null
     */
    public function createPaymentCustomer($account, $paymentCustomerModel = null)
    {
        if (!$paymentCustomerModel) {
            $paymentCustomerModel = Yii::$app->getModule('payment')->model('PaymentCustomer');
        }
        $studentCustomer = $this->createStripeCustomer($account->getDisplayName());
        $paymentCustomerModel->accountId = $account->id;
        $paymentCustomerModel->customerId = $studentCustomer->id;
        $paymentCustomerModel->save();
        return $paymentCustomerModel;
    }

    /**
     * @param $account
     * @return Balance
     */
    public function getBalance($account)
    {
        return Balance::retrieve(['stripe_account' => $account]);
    }

    public function removeCard($id, \modules\account\models\Account $account)
    {
        $model = Yii::$app->getModule('payment')->model('CardInfo');
        $model = $model::findOne(['stripeCustomerId' => $account->paymentCustomer->id, 'id' => $id]);
        if ($model) {
            $customer = Customer::retrieve($account->paymentCustomer->customerId);
            $customer->sources->retrieve($model->cardId)->delete();
            return (bool)$model->delete();
        }

        return false;
    }

    /**
     * @param $bankAccount PaymentBankAccount
     */
    public function removeBankAccount($bankAccount)
    {
        $customer = Customer::retrieve($bankAccount->paymentCustomer->customerId);
        $customer->sources->retrieve($bankAccount->paymentBankId)->delete();
    }

    /**
     * @deprecated
     * @param $customerId
     * @param $cardId
     * @return Customer
     */
    public function setActiveCardOnStripe($customerId, $cardId)
    {
        return $this->setActiveCardOrBankAccountOnStripe($customerId, $cardId);
    }

    /**
     * @param $customerId
     * @param $cardOrBankAccountId
     * @return Customer
     */
    public function setActiveCardOrBankAccountOnStripe($customerId, $cardOrBankAccountId)
    {
        $customer = Customer::retrieve($customerId);
        $customer->default_source = $cardOrBankAccountId;
        return $customer->save();
    }

    /**
     * @deprecated
     * @see CardInfo beforeSave
     */
    public function setActiveCard($id, $account = null)
    {
        /**
         * @var \modules\account\models\Account $account
         */
        $model = Yii::$app->getModule('payment')->model('CardInfo');
        if (!$account) {
            $account = Yii::$app->user->identity;
        }
        if ($account->isCrmAdmin()) {
            throw new \Exception('Can not use setActiveCard method for Companies. Only for Students and Company Clients. See setActiveCardOrBankAccountOnStripe');
        }
        $model = $model::findOne(['stripeCustomerId' => $account->paymentCustomer->id, 'id' => $id]);
        if (
            $model
            && (
                !$account->paymentCustomer->activeCard
                || $account->paymentCustomer->activeCard->id != $model->id
            )
        ) {
            $model->active = true;
            return $model->save(['active']);
        }

        return false;
    }

    /**
     * Is transaction already captured
     * @param $transaction
     * @return bool
     */
    public function isChargeCaptured($transaction)
    {
        return $this->retrieveCharge($transaction)->captured;
    }

    public function checkAccountCapability(string $stripeAccountId, $capabilityName): bool
    {
        $capability = Yii::$app->payment->getAccountCapability($stripeAccountId, $capabilityName);
        $capability = $capability->requirements->toArray();
        $isNothingThere = empty($capability['eventually_due'])
            && empty($capability['currently_due'])
            && empty($capability['past_due'])
            && empty($capability['pending_verification']);
        return $isNothingThere;
    }
    /**
     * @param $account \modules\account\models\Account
     * @param bool $debitNegativeBalance
     * @return Account
     */
    protected function createCustomer($account, bool $debitNegativeBalance = false)
    {
        $capabilities = [static::CAPABILITY_TRANSFER, static::CAPABILITY_CARD];
        $businessType = $account->isCrmAdmin() ? self::BUSINESS_TYPE_COMPANY : self::BUSINESS_TYPE_INDIVIDUAL;
        $createParams = [
            'country' => 'US',
            'type' => 'custom',
            'business_type' => $businessType,
            'requested_capabilities' => $capabilities,
            'settings' => [
                'payouts' => [
                    'debit_negative_balances' => $debitNegativeBalance,
                    'schedule' => [
                        'interval' => 'manual',
                    ],
                ],
            ],
            'email' => $account->email,
        ];
        if ($account->isTutor()) {
            $createParams['individual'] = [
                'email' => $account->email,
                'first_name' => $account->profile->firstName,
                'last_name' => $account->profile->lastName,
                'phone' => $account->profile->phoneNumber
            ];
        }

        $stripeAccount = Account::create($createParams);

        return $stripeAccount;
    }

    public function getExtraVerifyData($account = null)
    {
        $stripeAccount = $this->getStripeAccount($account);
        $fields = [];
        $requirements = $stripeAccount->requirements;
        if (!empty($requirements)) {
            foreach ($requirements->toArray() as $reason) {
                if (!is_array($reason) || empty($reason)) {
                    continue;
                }
                foreach ($reason as $field) {
                    if (empty($field)) {
                        continue;
                    }
                    $fields[] = $field;
                }
            }
        }
        $obj = new \stdClass();
        $obj->fields_needed = $fields;
        return $obj;
    }

    public function isVerified($account = null)
    {
        $stripeVerificationData = $this->getExtraVerifyData($account);
        return empty($stripeVerificationData->fields_needed);
    }

    /**
     * ensure paymentaccount
     * @param null|\modules\account\models\Account $account
     * @return null|\modules\account\models\Account
     */
    public function ensureAccount($account = null)
    {
        if ($account === null) {
            $account = $this->getIndentity();
        }
        return $account;
    }

    /**
     * @param null| UserAccount $account
     * @return Account
     * @throws \Exception
     */
    public function getStripeAccount($account = null)
    {
        $account = $this->ensureAccount($account);

        $paymentAccount = \modules\payment\models\Account::findOne(['accountId' => $account->id]);

        if (!$paymentAccount) {
            $paymentAccount = $this->createAccount($account->id);
        }

        if (!$paymentAccount) {
            throw new \Exception('Can\'t create payment account');
        }
        return $this->getStripeAccountById($paymentAccount->paymentAccountId);
    }

    public static function getAccountCapability(string $stripeAccountId, string $capability)
    {
        return Account::retrieveCapability($stripeAccountId, $capability);
    }

    /**
     * @param integer $bankAccountId
     * @param Account $account
     * @return BankAccount
     * @throws \Exception
     */
    public function getBankAccount($bankAccountId, $account = null)
    {
        $stripeAccount = $this->getStripeAccount($account);

        return $stripeAccount->external_accounts->retrieve($bankAccountId);
    }

    /**
     * Delete external bank account from stripe
     * @param $bankAccountId
     * @return \Stripe\ExternalAccount
     * @throws \Exception
     */
    public function deleteBankAccount($bankAccountId)
    {
        return $this->getBankAccount($bankAccountId)->delete();
    }

    public function getStripeAccountById($paymentAccountId)
    {
        return \Stripe\Account::retrieve($paymentAccountId);
    }

    public function setPersonalNumber($personalNumberToken, &$error)
    {
        $stripeAccount = $this->getStripeAccount();
        $stripeAccount->legal_entity->personal_id_number = $personalNumberToken;
        try {
            return $stripeAccount->save();
        } catch (\Exception $e) {
            Yii::error('Failed to save PII token. '  . $e->getMessage() . "\n" . $e->getTraceAsString(), 'payment');
            $error = $e->getMessage();
            return false;
        }
    }

    public function getIndentity()
    {
        $identity = Yii::$app->user->identity;
        return $identity;
    }

    public function uploadDocument(UploadedFile $document)
    {
        $stripeAccount = $this->getStripeAccount();

        $identity = $this->getIndentity();
        $paymentAccount = $identity->paymentAccount;

        try {
            $fp = fopen($document->tempName, 'r');
            $file_obj = \Stripe\FileUpload::create(
                array(
                    "purpose" => "identity_document",
                    "file" => $fp
                ),
                array(
                    "stripe_account" => $stripeAccount->id,
                )
            );
            if (empty($file_obj)) {
                throw new \Exception('Failed to upload file. Please try again or contact us.');
            }
            $file = $file_obj->id;
            $stripeAccount->legal_entity->verification->document = $file;
            $stripeAccount->save();
            $paymentAccount->fileToken = $file_obj->id;
            $paymentAccount->save(false);
        } catch (\Exception $e) {
            $paymentAccount->addError('', $e->getMessage());
        }

        return $paymentAccount;
    }

    /**
     * Capture(Charge) holt earlier money. Not used for Lessons anymore
     *
     * @param $transaction integer Stripe Transaction ID
     * @return mixed|\Stripe\Charge
     */
    public function capture($transaction)
    {
        $charge = $this->retrieveCharge($transaction);
        return $charge->capture();
    }

    /**
     * Get Stripe Charge object
     *
     * @param $transaction integer Stripe Transaction ID
     * @return \Stripe\Charge
     */
    public function retrieveCharge($transaction)
    {
        return \Stripe\Charge::retrieve($transaction);
    }

    /**
     * @param $eventId
     * @param $paymentAccountId
     * @return Event
     */
    public function getEvent($eventId, $paymentAccountId)
    {
        return Event::retrieve($eventId, ['stripe_account' => $paymentAccountId]);
    }

    /**
     * @param $capture boolean Whether to capture money now or just Hold them first (if false) - Hold option not used for lessons anymore
     * @param $customer string Stripe Customer ID
     * @param $destination string Stripe Connected account ID
     * @param $amount integer Charge amount in cents
     * @param $applicationFee integer Charge Fee in cents that will be sent to main HT Stripe account
     * @param $description string Charge description that will be shown on Stripe and on customer's bank side
     * @param $chargeStripeFeeOnConnectedAccount boolean charge Stripe fee from Connected Account (if true) or from Platform Account (if false)
     * @param $companyName string name of company (for client balance transactions)
     * @return mixed|\Stripe\Charge
     */
    public function charge($capture, $customer, $destination, $amount, $applicationFee, $description, $chargeStripeFeeOnConnectedAccount = false, $companyName = null)
    {
        $params = [
            "amount" => $amount,
            "currency" => static::USD_CURRENCY,
            "capture" => $capture,
            "application_fee_amount" => $applicationFee,
            "description" => $description,
        ];
        if (!empty($companyName)) {
            $params['statement_descriptor'] =  $companyName;
        }
        if (!$chargeStripeFeeOnConnectedAccount) {
            return \Stripe\Charge::create(array_merge($params, ["destination" => $destination, "customer" => $customer,]));
        } else {
            $token = \Stripe\Token::create(array("customer" => $customer), ["stripe_account" => $destination]);
            return \Stripe\Charge::create(array_merge($params, ['source' => $token]), ["stripe_account" => $destination]);
        }
    }

    /**
     * using for charge funds from company to platform in group payments
     * @param $capture
     * @param $customer
     * @param $amount
     * @param $description
     * @return \Stripe\Charge
     */
    public function chargeToPlatformAccount($capture, $customer, $amount, $description)
    {
        $params = [
            "amount" => $amount,
            "currency" => static::USD_CURRENCY,
            "capture" => $capture,
            "description" => $description,
        ];
        return \Stripe\Charge::create(array_merge($params, ["customer" => $customer,]));
    }

    /**using for refund lesson transfers (when company process batch Payments)
     * @param $transferId
     * @param null $amount - set amount for partially reverse
     */
    public function reversTransfer($transferId, $amount = null)
    {
        $transfer = Transfer::retrieve($transferId);
        $params = [];
        if (!empty($amount)) {
            $params['amount'] = $amount;
        }
        return $transfer->reversals->create($params);
    }

    /**
     * Direct charge from StripeCustomer to main Stripe HT account
     * @param $customer
     * @param $amount
     * @param $description
     * @return \Stripe\Charge
     */
    public function directCharge($customer, $amount, $description)
    {
        return \Stripe\Charge::create([
            "amount" => $amount,
            "currency" => static::USD_CURRENCY,
            "customer" => $customer,
            "description" => $description,
        ]);
    }

    /**
     * @param Transaction $transaction
     * @param double $amount (if amount not null - it's a partial refund)
     * @param bool $refundTransfer
     * @param bool $refundFee
     * @return \Stripe\Refund
     * @throws \Exception
     */
    public function refund(Transaction $transaction, $amount = null, $refundTransfer = true, $refundFee = true)
    {
        if (!empty($amount) && !$transaction->isClientBalance() && !$transaction->isGroupChargeTransaction()) {
            throw new \Exception('Partial refund allowed only for client balance and group charge transactions.');
        }
        $params = array(
            "charge" => $transaction->transactionExternalId,
        );

        if ($amount) {
            $params = array_merge($params, [
                'amount' => $amount,
            ]);
        }
        $refund = \Stripe\Refund::create($params, ($transaction->isClientBalance() && !empty($destination)) ? ['stripe_account' => $destination] : null);

        $this->processDataRefund($transaction, $amount, $refund);

        return $refund;
    }

    private function processDataRefund(Transaction $transaction, $amount, $refund)
    {
        /*crating new transaction if it's a partial refund*/
        if ($amount) {
            $parentId = $transaction->id;
            $transaction = clone($transaction);
            $transaction->id = null;
            $transaction->isNewRecord = true;
            $transaction->parentId = $parentId;
            $transaction->refresh();
            $transaction->type = Transaction::PARTIAL_REFUND;
            /*convert amount from cent to dollars*/
            $transaction->amount = Transaction::amountToDollars($amount);
            $transaction->createdAt = date('Y-m-d');

            if ($transaction->isGroupChargeTransaction()) {
                //in process partial refund of group transaction need to know
                //what lesson was refunded, that's why objectType for partial refund has been changed to lesson
                //(to store lesson)
                $transaction->objectType = Transaction::TYPE_LESSON;
                $transaction->objectId = $this->refundLesson->id;
                $transaction->studentId = $this->refundLesson->studentId;
                $transaction->tutorId = $this->refundLesson->tutorId;
            }
        } else {
            $transaction->type = Transaction::STRIPE_REFUND;
        }
        /**
         * @var Formatter $formatter
         */
        $formatter = Yii::$app->formatter;
        //same for full and partial refund
        $transaction->transactionExternalId = $refund->id;
        $transaction->response = $refund;
        $transaction->processDate = date($formatter->MYSQL_DATE);
        $transaction->status = Module::selectTransactionStatus($refund->status);
        $transaction->refundInitiator = Yii::$app->user->id;
        $transaction->createdAt = date($formatter->MYSQL_DATETIME);
        $transaction->save(false);

        //if status of refund is pending process it via webhook handler charge.refunded
        if ($transaction->isStatusPending()) {
            return;
        }

        if ($transaction->isClientBalance()) {
            //using $amount for client-balance if it was partial-refund transaction or $transaction->amount if it was full-refund
            $clientBalanceAmount = -1 *  $transaction->amount;

            /*Create Client-balance transaction with type Automatically */
            $clientBalanceTransaction = new ClientBalanceTransaction([
                'clientId' => $transaction->studentId,
                'amount' => $clientBalanceAmount,
                'type' => ClientBalanceTransaction::TYPE_TRANSACTION_AUTO,
                'transactionId' => $transaction->id,
                'hide' => 1,
            ]);
            if (!$clientBalanceTransaction->save()) {
                Yii::error('Failed to save client balance transaction (REFUND). Errors: ' . json_encode($clientBalanceTransaction->getErrors()), 'payment');
                throw new \Exception('Failed to save client balance transaction (REFUND).');
            }
        }

        if ($transaction->isLesson()) {
            $lesson = $transaction->lesson;
            $lesson->saveRefundedStatus();
            //if user is company client - add funds to balance
            Module::createRefundClientBalance($transaction);
        }

        $moduleTransaction = Yii::$app->getModule('payment');
        $moduleTransaction->eventRefundProcessed($transaction);
    }

    /**
     * @see Init extension default
     */
    public function init()
    {
        if (!$this->publicKey) {
            throw new Exception("Stripe's public key is not set.");
        } elseif (!$this->privateKey) {
            throw new Exception("Stripe's private key is not set.");
        }

        Stripe::setApiKey($this->privateKey);

        if ($this->apiVersion) {
            Stripe::setApiVersion($this->apiVersion);
        }

        parent::init();
    }

    public function addNewCapabilities($stripeAccountId)
    {
        Account::update($stripeAccountId, ['requested_capabilities' => [self::CAPABILITY_CARD, self::CAPABILITY_TRANSFER]]);
        $paymentAccount = PaymentAccount::find()->andWhere(['paymentAccountId' => $stripeAccountId])->one();
        $account = $paymentAccount->account;
        if ($account->isTutor()) {
            Account::update($stripeAccountId, [
                'individual' => [
                    'email' => $account->email,
                    'phone' => $account->profile->phoneNumber,
                ],
            ]);
        }
    }

    public function deleteAccount($stripeAccountId)
    {
        $account = Account::retrieve($stripeAccountId);
        $account->delete();
    }
}
