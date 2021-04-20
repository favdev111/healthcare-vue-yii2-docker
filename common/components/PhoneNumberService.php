<?php

namespace common\components;

use yii\base\Component;
use Yii;

/**
 * Class PhoneNumberService
 * @package common\components
 */
class PhoneNumberService extends Component
{
    protected $defaultPhoneNumber;
    protected $defaultPhoneNumberFormatted;

    protected $defaultTutorPhoneNumber;
    protected $defaultTutorPhoneNumberFormatted;

    protected $callUsPhoneNumber;
    protected $callUsPhoneNumberFormatted;

    protected $tutorRoutes = [
        'account/default/login',
        'account/job-search/index',
        'account/job/apply-job',
    ];

    private $_phoneNumber;
    private $_phoneNumberFormatted;

    public function init()
    {
        parent::init();

        if (preg_match('[^0-9]', $this->defaultPhoneNumber) !== false) {
            $this->defaultPhoneNumberFormatted = $this->defaultPhoneNumber;
            $this->defaultPhoneNumber = preg_replace('/[^0-9]/', '', $this->defaultPhoneNumber);
        }

        if (preg_match('[^0-9]', $this->defaultTutorPhoneNumber) !== false) {
            $this->defaultTutorPhoneNumberFormatted = $this->defaultTutorPhoneNumber;
            $this->defaultTutorPhoneNumber = preg_replace('/[^0-9]/', '', $this->defaultTutorPhoneNumber);
        }

        if (preg_match('[^0-9]', $this->callUsPhoneNumber) !== false) {
            $this->callUsPhoneNumberFormatted = $this->callUsPhoneNumber;
            $this->callUsPhoneNumber = preg_replace('/[^0-9]/', '', $this->callUsPhoneNumber);
        }
    }

    public function getDefaultPhoneNumber(): string
    {
        return $this->defaultPhoneNumber;
    }

    public function getDefaultTutorPhoneNumber(): string
    {
        return $this->defaultTutorPhoneNumber;
    }

    public function getCallUsPhoneNumber(): string
    {
        return $this->callUsPhoneNumber;
    }

    public function setCallUsPhoneNumber(string $value)
    {
        $this->callUsPhoneNumber = $value;
    }


    public function getCallUsPhoneNumberFormatted(): string
    {
        return $this->callUsPhoneNumberFormatted;
    }

    public function setCallUsPhoneNumberFormatted(string $value)
    {
        $this->callUsPhoneNumberFormatted = $value;
    }

    public function setDefaultPhoneNumber(string $value)
    {
        $this->defaultPhoneNumber = $value;
    }

    public function setDefaultTutorPhoneNumber(string $value)
    {
        $this->defaultTutorPhoneNumber = $value;
    }

    public function getDefaultPhoneNumberFormatted(): string
    {
        if ($this->defaultPhoneNumberFormatted === null) {
            $this->defaultPhoneNumberFormatted = $this->formatPhoneNumber($this->defaultPhoneNumber);
        }

        return $this->defaultPhoneNumberFormatted;
    }

    public function getDefaultTutorPhoneNumberFormatted(): string
    {
        if ($this->defaultTutorPhoneNumberFormatted === null) {
            $this->defaultTutorPhoneNumberFormatted = $this->formatPhoneNumber($this->defaultTutorPhoneNumber);
        }

        return $this->defaultTutorPhoneNumberFormatted;
    }

    public function getPhoneNumber(): string
    {
        if (null === $this->_phoneNumber) {
            if ($this->isTutorRoute()) {
                $this->_phoneNumber = $this->getDefaultTutorPhoneNumber();
            } else {
                $this->_phoneNumber = $this->getDefaultPhoneNumber();
            }
        }

        return $this->_phoneNumber;
    }

    public function getPhoneNumberFormatted(): string
    {
        if (null === $this->_phoneNumberFormatted) {
            if ($this->isTutorRoute()) {
                $this->_phoneNumberFormatted = $this->getDefaultTutorPhoneNumberFormatted();
            } else {
                $this->_phoneNumberFormatted = $this->getDefaultPhoneNumberFormatted();
            }
        }

        return $this->_phoneNumberFormatted;
    }

    protected function isTutorRoute(): bool
    {
        if (
            (
                ($user = Yii::$app->user)
                && !$user->isGuest
            )
            || in_array(Yii::$app->requestedRoute, $this->tutorRoutes)
        ) {
            return true;
        }

        return false;
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = substr_replace($phone, '-', 3, 0);
        return substr_replace($phone, '-', 7, 0);
    }
}
