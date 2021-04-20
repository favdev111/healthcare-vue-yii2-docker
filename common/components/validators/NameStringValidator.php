<?php

namespace common\components\validators;

use Yii;
use yii\validators\Validator;

class NameStringValidator extends Validator
{
    /**
     * @var string
     */
    public $message = 'Your {attribute} can only contain letters characters, whitespace, hyphen, apostrophe.';
    /**
     * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
     * If this property is not set, [[\yii\base\Application::charset]] will be used.
     */
    public $encoding;

    /**
     * @var bool Apply filter trim to value
     */
    public $filterTrim = true;

    /**
     * @var string user-defined error message used when the length of the value is greater than [[max]].
     */
    public $tooLong;

    /**
     * @var int maximum length. If not set, it means no maximum length limit.
     * @see tooLong for the customized message for a too long string.
     */
    public $maxLength = 254;

    public function init()
    {
        if ($this->encoding === null) {
            $this->encoding = Yii::$app ? Yii::$app->charset : 'UTF-8';
        }

        if ($this->tooLong === null) {
            $this->tooLong = Yii::t('yii', '{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.');
        }

        parent::init();
    }

    public function validateAttribute($model, $attribute)
    {
        $value = (string)$model->$attribute;
        if ($this->filterTrim) {
            $value = trim($value);
        }

        $value = mb_convert_case(mb_strtolower($value), \MB_CASE_TITLE, $this->encoding);

        $model->$attribute = $value;

        $length = mb_strlen($value, $this->encoding);
        if ($this->maxLength !== null && $length > $this->maxLength) {
            $this->addError(
                $model,
                $attribute,
                $this->tooLong,
                ['max' => $this->maxLength]
            );
        }

        if (!preg_match('/^[a-z A-Z-\\\']+$/', $model->$attribute)) {
            $this->addError($model, $attribute, $this->message, ['attribute' => $attribute]);
        }
    }
}
