<?php

namespace common\components;

use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\NotSupportedException;
use yii\base\UnknownPropertyException;

abstract class ImmutableObject implements Arrayable
{
    use ArrayableTrait;

    protected $_attributes;

    abstract protected function init(array $config = []);

    public function __construct(array $config = [])
    {
        $this->_attributes = new \stdClass();
        $this->init($config);
    }

    /**
     * PHP getter magic method.
     * Atributes can be accessed like properties.
     *
     * @param string $name property name
     * @throws \yii\base\UnknownPropertyException if getting unknown property
     * @return mixed property value
     * @see getAttribute()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        if (property_exists($this->_attributes, $name)) {
            return $this->_attributes->$name;
        }

        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Sets value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     * @param string $name the property name or the event name
     * @param mixed $value the property value
     * @throws NotSupportedException Can not set property
     */
    public function __set($name, $value)
    {
        throw new NotSupportedException('Can not set attributes');
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking if the named attribute is `null` or not.
     * @param string $name the property name or the event name
     * @return bool whether the property value is null
     */
    public function __isset($name)
    {
        try {
            return $this->__get($name) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function __sleep()
    {
        return ['_attributes'];
    }

    public function attributes()
    {
        return (array)$this->_attributes;
    }

    public function fields()
    {
        $fields = array_merge(
            array_keys($this->attributes()),
            $this->getDynamicAttributes()
        );
        return array_combine($fields, $fields);
    }

    protected function getDynamicAttributes()
    {
        $class = new \ReflectionClass($this);
        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }
}
