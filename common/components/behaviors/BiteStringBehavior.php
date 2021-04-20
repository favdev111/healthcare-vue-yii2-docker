<?php

namespace common\components\behaviors;

use common\components\ActiveRecord;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Class BiteStringBehavior
 * @property  $attributeToSave string - attribute that over using as data storage
 * @property $attributeToDisplay array - array with 0 or 1 elements using for displaying data
 * @property $biteStringLength integer - length of bite string (attributeToSave)
 * @package common\components\behaviors
 */
class BiteStringBehavior extends Behavior
{
    public $attributeToSave;
    public $attributeToDisplay;
    public $biteStringLength;
    public function events()
    {
        return [
          ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSavingData',
          ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSavingData',
          ActiveRecord::EVENT_AFTER_FIND => 'beforeDisplayingData'
        ];
    }

    public function init()
    {
        if (empty($this->attributeToSave) || empty($this->attributeToDisplay) || empty($this->biteStringLength)) {
            throw new InvalidConfigException('Not all behavior attributes has been set');
        }
        parent::init();
    }

    public function beforeSavingData()
    {
        $owner = $this->owner;
        $displayedData = $owner->{$this->attributeToDisplay};

        $decimalString = '';
        for ($i = 1; $i <= $this->biteStringLength; $i++) {
            if (isset($displayedData[$i]) && !empty($displayedData[$i])) {
                $decimalString .= '1';
            } else {
                $decimalString .= '0';
            }
        }
        $newValue = bindec($decimalString);
        //if value has been changed
        if ($owner->{$this->attributeToSave} != $newValue) {
            $owner->{$this->attributeToSave} = $newValue;
        }
    }

    public function beforeDisplayingData()
    {
        $owner = $this->owner;
        //getting data from owner
        $storedData = $owner->{$this->attributeToSave};
        //transform to bite string
        $storedData = decbin($storedData);
        //split to array
        $storedData = str_split($storedData);
        $count = count($storedData);
        //add zero elements to start of array if it needs
        if ($count < $this->biteStringLength) {
            $extraZero = array_fill(0, $this->biteStringLength - $count, 0);
            foreach ($extraZero as $zero) {
                array_unshift($storedData, $zero);
            }
        }
        //convert bite string to array
        $preparedData = [];
        foreach ($storedData as $key => $value) {
            if ($value == 1) {
                $preparedData[$key + 1] = $key + 1;
            }
        }
        //assign converted data to owner
        $owner->{$this->attributeToDisplay} = $preparedData;
    }
}
