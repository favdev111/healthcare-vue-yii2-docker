<?php

namespace common\components\validators;

use common\components\api\services\NPIClient;
use Yii;
use yii\helpers\ArrayHelper;
use yii\validators\NumberValidator;
use yii\validators\Validator;

/**
 * Class NPIValidator
 * @package common\components\validators
 */
class NPIValidator extends Validator
{
    /**
     * @var string
     */
    public $message = '{attribute} is invalid.';
    /**
     * @var NPIClient
     */
    protected $client;

    /**
     * NPIValidator constructor.
     * @param NPIClient $client
     * @param array $config
     */
    public function __construct(NPIClient $client, $config = [])
    {
        $this->client = $client;
        parent::__construct($config);
    }

    /**
     * @param mixed $value
     * @return array|void|null
     * @throws \Exception
     */
    protected function validateValue($value)
    {
        $numberValidator = Yii::createObject([
            'class' => NumberValidator::class,
            'min' => 0,
            'max' => 9999999999,
            'integerOnly' => true,
        ]);

        if (!$numberValidator->validate($value)) {
            return [$this->message, []];
        }
        $data = $this->client->getByNumber($value);

        return ArrayHelper::getValue($data, 'results.0') ? null : [$this->message, []];
    }
}
