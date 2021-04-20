<?php

namespace modules\account\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * Processing related AccountPhone and AccountEmailModels
 *
 * Class AdditionalDataBehavior
 * @package modules\account\behaviors
 */
class AdditionalDataBehavior extends Behavior
{
    public $accountEmailsClass;
    public $accountPhonesClass;

    //array which should be filled from post
    public $phoneNumbers;
    public $emails;
    //array of validated models created from post data
    public $phoneNumberModels;
    public $emailModels;

    public $phoneNumberPrimaryModel;
    public $primaryEmailModel;

    public function init()
    {
        parent::init();

        if (empty($this->accountPhonesClass)) {
            throw new InvalidConfigException('accountPhonesClass hasn\'t been set.');
        }

        if (empty($this->accountEmailsClass)) {
            throw new InvalidConfigException('accountEmailsClass hasn\'t been set.');
        }
    }

    //need merge with owner validation rules
    public static function validationRules()
    {
        return [
            [['emails', 'phoneNumbers'], 'required'],
            [['emails'], 'emailStructureValidator'],
            [['phoneNumbers'], 'phoneStructureValidator'],
            [['emails', 'phoneNumbers'], 'onePrimaryValidator'],
            [['emails'], 'validateAndCreateEmailsModels'],
            [['phoneNumbers'], 'validateAndCreatePhoneModels'],
        ];
    }

    /**
     * @param string $attribute
     */
    public function emailStructureValidator(string $attribute): void
    {
        $this->validateStructure($attribute, 'email');
    }

    /**
     * @param string $attribute
     */
    public function phoneStructureValidator(string $attribute): void
    {
        $this->validateStructure($attribute, 'phoneNumber');
    }

    /**
     * @param string $arrayAttribute
     * @param string $attributeWhichShouldBeInArray
     */
    protected function validateStructure(string $arrayAttribute, string $attributeWhichShouldBeInArray): void
    {
        $owner = $this->owner;
        $arrayData = $owner->{$arrayAttribute};
        if (is_array($arrayData)) {
            foreach ($arrayData as &$arrayItem) {
                if (!isset($arrayItem[$attributeWhichShouldBeInArray]) || !isset($arrayItem['isPrimary'])) {
                    $this->addErrorInvalidStructure($arrayAttribute);
                }
            }
        } else {
            $this->addErrorInvalidStructure($arrayAttribute);
        }
    }

    protected function addErrorInvalidStructure($attribute): void
    {
        $this->owner->addError($attribute, 'Structure of ' . $attribute . ' is invalid.');
    }


    /**
     * Only one row should be primary
     * @param string $attribute
     */
    public function onePrimaryValidator(string $attribute)
    {
        $owner = $this->owner;
        $primaryCount = 0;
        foreach ($owner->$attribute as $field) {
            if ($field['isPrimary']) {
                $primaryCount++;
            }
        }
        if ($primaryCount > 1) {
            $owner->addError($attribute, 'Only one of ' . $attribute . ' must be primary.');
        }

        if ($primaryCount == 0) {
            $owner->addError($attribute, 'At least one of ' . $attribute . ' must be primary.');
        }
    }

    /**
     * @param string $class
     * @param string $dataArrayAttribute
     * @param string $modelsArrayAttribute
     * @param string $primaryModelAttribute
     * @param string $attribute
     */
    protected function validateAndCreateMultipleModels(string $class, string $dataArrayAttribute, string $modelsArrayAttribute, string $primaryModelAttribute, string $attribute): void
    {
        $owner = $this->owner;
        $models = [];
        $dataToProcess = [];
        $dataToProcess['additional'] = [];
        $dataToProcess['primary'] = '';
        foreach ($owner->{$dataArrayAttribute} as $data) {
            $isExistInAdditionalList = in_array($data[$attribute], $dataToProcess['additional']);
            if (
                (
                    //if same primary number has been added
                    $data['isPrimary']
                     && $dataToProcess['primary'] == $data[$attribute]
                )
                ||
                (
                    //or it's duplicate of additional data
                    !$data['isPrimary']
                    && ($isExistInAdditionalList || $dataToProcess['primary'] == $data[$attribute])
                )
                || empty($data)
            ) {
                continue;
            }

            //if it's additional data which become primary - remove it from additional data list
            if ($data['isPrimary'] && $isExistInAdditionalList) {
                $key = array_search($data[$attribute], $dataToProcess['additional']);
                unset($dataToProcess['additional'][$key]);
            }

            if ($data['isPrimary']) {
                $dataToProcess['primary'] = $data[$attribute];
            } else {
                $dataToProcess['additional'][] = $data[$attribute];
            }
        }

        $models[] = $this->createModel(
            $class,
            $dataToProcess['primary'],
            true,
            $attribute,
            $primaryModelAttribute
        );

        foreach ($dataToProcess['additional'] as $phoneNumber) {
            $models[] = $this->createModel(
                $class,
                $phoneNumber,
                false,
                $attribute,
                $primaryModelAttribute
            );
        }

        $owner->{$modelsArrayAttribute} = $models;

        if (!Model::validateMultiple($owner->{$modelsArrayAttribute}, [$attribute])) {
            foreach ($owner->{$modelsArrayAttribute} as $model) {
                if ($model->hasErrors()) {
                    $this->owner->addErrors($model->getErrors());
                }
            }
        }
    }

    protected function createModel($class, $value, $isPrimary, $attribute, $primaryModelAttribute)
    {
        $model = new $class();
        $model->$attribute = $value;
        $model->isPrimary = $isPrimary;
        if ($model->isPrimary) {
            $this->owner->{$primaryModelAttribute} = $model;
        }
        return $model;
    }

    public function validateAndCreatePhoneModels()
    {
        $this->validateAndCreateMultipleModels(
            $this->accountPhonesClass,
            'phoneNumbers',
            'phoneNumberModels',
            'phoneNumberPrimaryModel',
            'phoneNumber'
        );
    }

    public function validateAndCreateEmailsModels()
    {
        $this->validateAndCreateMultipleModels(
            $this->accountEmailsClass,
            'emails',
            'emailModels',
            'primaryEmailModel',
            'email'
        );
    }


    //save models
    public function saveEmails(bool $isCreate, Model $account): void
    {
        $this->processMultipleModels($this->accountEmailsClass, $isCreate, $this->owner->emailModels, 'email', $account, $account->getAccountEmails());
    }

    public function savePhones(bool $isCreate, Model $account): void
    {
        $this->processMultipleModels($this->accountPhonesClass, $isCreate, $this->owner->phoneNumberModels, 'phoneNumber', $account, $account->getAccountPhones());
    }


    /**
     * Fill accountId and save models from array
     * @param array $models
     * @param int $accountId
     */
    protected function createModels(array $models, int $accountId): void
    {
        foreach ($models as $model) {
            $model->accountId = $accountId;
            $model->save(false);
        }
    }

    /**
     * @param string $class
     * @param bool $isCreate
     * @param array $models
     * @param string $field
     * @param Model $accountModel
     * @param ActiveQuery $dbDataQuery
     */
    protected function processMultipleModels(string $class, bool $isCreate, array $models, string $field, Model $accountModel, ActiveQuery $dbDataQuery): void
    {
        //save models in case of create action
        /*
         * for update action need:
        A) update primary status for rows that already in DB
        B) delete from DB rows which aren't exist in new data list (from post)
        C) create rows in DB for new data
         */
        if ($isCreate) {
            $this->createModels($models, $accountModel->id);
        } else {
            //in case of update looking for data in DB
            $dataFromDb = $dbDataQuery->select($field)->column();

            $modelsFromDb = $class::find()->andWhere([$field => $dataFromDb, 'accountId' => $accountModel->id])->all();
            foreach ($modelsFromDb as $k => $modelFromDb) {
                //is model not in model list from db (comparing with new models from post) remove it
                $stillExists = false;
                foreach ($models as $j => $model) {
                    if ($model->$field == $modelFromDb->$field) {
                        //in this case $model contains data about phone that already exists in db
                        $stillExists = true;
                        break;
                    }
                }
                if ($stillExists) {
                    //A) update primary status for rows that already in DB
                    $modelFromDb->isPrimary = $model->isPrimary;
                    $modelFromDb->save(false);
                    unset($models[$j]);
                } else {
                    //B) delete from DB rows which aren't exist in new data list (from post)
                    $modelFromDb->delete();
                    unset($modelsFromDb[$k]);
                }
            }

            if (!empty($models)) {
                //C) create rows in DB for new data
                $this->createModels($models, $accountModel->id);
            }
        }
    }
}
