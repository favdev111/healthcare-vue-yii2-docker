<?php

namespace common\components;

use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Class JsonSerializer
 *
 * @package common\components
 */
class JsonSerializer extends \yii\queue\serializers\JsonSerializer
{
    /**
     * @param mixed $data
     * @return array|mixed
     * @throws InvalidConfigException
     */
    protected function toArray($data)
    {
        if (is_object($data) && $data instanceof Model) {
            $result = [$this->classKey => get_class($data)];
            foreach ($data->attributes as $property => $value) {
                if ($property === $this->classKey) {
                    throw new InvalidConfigException("Object cannot contain $this->classKey property.");
                }
                $result[$property] = $this->toArray($value);
            }

            return $result;
        }

        return parent::toArray($data);
    }
}
