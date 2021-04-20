<?php

namespace modules\payment\components;

use modules\payment\models\Transaction;
use yii\base\BaseObject;
use yii\console\Application;
use yii\console\Controller as ConsoleController;
use yii\helpers\Console;
use Yii;

/**
 * Use only on console context
 * Class AbstractHandlerService
 * @package modules\payment\components
 * @todo Need refactoring. Every step of payment processing should be shown in console by \yii\console\Controller stdout method
 */
abstract class AbstractHandlerService extends BaseObject
{
    /**
     * @var $transactions Transaction[]
     */
    private $transactions;

    /**
     * @var $application Application
     */
    private $application;

    /**
     * @var $logger
     */
    private $logger;

    /**
     * @var Payment $payment
     */
    protected $payment;

    /**
     * AbstractHandlerService constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->setApplication(Yii::$app);
    }

    /**
     * Setter for console app
     * @param $application
     * @return $this
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * Getter for console app
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Setter for transactions
     * @param array $transactions
     * @return $this
     */
    public function setTransactions(array $transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }

    /**
     * Getter for array of Transaction
     * @return Transaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Send console message
     * @param $message
     * @return $this
     */
    protected function sendStdout($message)
    {
        if ($this->application->controller instanceof ConsoleController) {
            $this->application->controller->stdout($message, Console::BOLD);
        }
        return $this;
    }


    /**
     * Set attributes and save transaction model
     * @param Transaction $transaction
     * @param array $attributes
     * @param bool $runValidation save without
     * @return Transaction
     */
    protected function saveTransaction(Transaction $transaction, array $attributes, $runValidation = true)
    {
        $transaction->setAttributes($attributes, false);
        $transaction->save($runValidation);
    }

    /**
     * Run processing transactions
     * @return mixed
     */
    abstract public function run();
}
