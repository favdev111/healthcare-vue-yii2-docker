<?php

namespace modules\account\models;

trait ChangeLogTrait
{
    public function changeLog(
        string $fieldName,
        string $logClass,
        int $objectId,
        array $changedAttributes,
        string $additionalComment = ''
    ) {
        /**
         * @var $logInstance ChangeLog
         */
        if (array_key_exists($fieldName, $changedAttributes)) {
            $logInstance = new $logClass();
            $logInstance->comment = $additionalComment;
            $logInstance->madeByAccount = \Yii::$app->user->identity ?? null;
            $logInstance->objectId = $objectId;
            $logInstance->oldValue = [$changedAttributes[$fieldName]];
            $logInstance->newValue = [$this->$fieldName];
            $logInstance->save(false);
        }
    }
}
