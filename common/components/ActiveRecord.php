<?php

namespace common\components;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use JsonSchema\Exception\InvalidArgumentException;
use Yii;
use yii\db\StaleObjectException;
use yii\helpers\Json;

class ActiveRecord extends \yii\db\ActiveRecord
{
    /*
     * Module related with AR
     */
    public $module;

    /**
     * @var array
     */
    private $changedAttributes = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The built-in, primitive cast types supported by Eloquent.
     *
     * @var string[]
     */
    protected static $primitiveCastTypes = [
        'array',
        'bool',
        'boolean',
        'collection',
        'custom_datetime',
        'date',
        'datetime',
        'decimal',
        'double',
        'float',
        'int',
        'integer',
        'json',
        'object',
        'real',
        'string',
        'timestamp',
    ];

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->changedAttributes = array_keys($this->dirtyAttributes);

        return parent::beforeSave($insert);
    }


    /**
     * When validation is disabled
     */
    public function saveErrorLog()
    {
        $attributes = Json::encode($this->attributes);
        Yii::error('Failed to save ' . static::class . ' model. Attributes: ' . $attributes);
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $isModelSaved = parent::save($runValidation, $attributeNames);
        if ($isModelSaved === false && $runValidation === false) {
            $this->saveErrorLog();
        }

        return $isModelSaved;
    }

    /**
     * @return array
     */
    public function getChangedAttributes()
    {
        return $this->changedAttributes;
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts;
    }

    /**
     * Determine whether an attribute should be cast to a native type.
     *
     * @param  string  $key
     * @param  array|string|null  $types
     * @return bool
     */
    public function hasCast($key, $types = null)
    {
        if (array_key_exists($key, $this->getCasts())) {
            return $types ? in_array($this->getCastType($key), (array) $types, true) : true;
        }

        return false;
    }

    /**
     * @param array|null $attributes
     *
     * @return array
     */
    protected function getDatabaseFormattedValues(array $attributes = null): array
    {
        $values = $this->getDirtyAttributes($attributes);
        foreach ($this->getCasts() as $attribute => $type) {
            if (!isset($values[$attribute]) || null === $values[$attribute]) {
                continue;
            }

            $value = $values[$attribute];

            if (! is_null($value) && $this->isJsonCastable($attribute)) {
                $value = $this->castAttributeAsJson($attribute, $value);
            }

            if (
                in_array($type, ['date', 'datetime', 'timestamp'])
                && ($value instanceof Carbon)
            ) {
                switch ($type) {
                    case 'array':
                    case 'json':
                        $values[$attribute] = $this->castAttributeAsJson($attribute, $value);
                        break;

                    case 'date':
                        $values[$attribute] = $value->format('Y-m-d');
                        break;

                    case 'timestamp':
                    case 'datetime':
                        $values[$attribute] = $value->format('Y-m-d H:m:s');
                        break;
                }
            }
        }

        return $values;
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        $castType = $this->getCastType($key);

        if (is_null($value) && in_array($castType, static::$primitiveCastTypes)) {
            return $value;
        }

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return $this->fromFloat($value);
            case 'decimal':
                return $this->asDecimal($value, explode(':', $this->getCasts()[$key], 2)[1]);
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return $this->fromJson($value, true);
            case 'array':
            case 'json':
                return $this->fromJson($value);
            case 'date':
                return $this->asDate($value);
            case 'datetime':
            case 'custom_datetime':
                return $this->asDateTime($value);
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function castAttributes(): array
    {
        $castedValues = [];
        $values = $this->getAttributes();
        foreach ($this->getCasts() as $attribute => $type) {
            if (!isset($values[$attribute]) || null === $values[$attribute]) {
                continue;
            }

            $castedValues[$attribute] = $this->castAttribute($attribute, $values[$attribute]);
        }

        return $castedValues;
    }

    /**
     * @inheritDoc
     */
    protected function insertInternal($attributes = null)
    {
        if (!$this->beforeSave(true)) {
            return false;
        }

        $values = $this->getDirtyAttributes();
        $insertValues = $this->getDatabaseFormattedValues($attributes);

        if (($primaryKeys = static::getDb()->schema->insert(static::tableName(), $insertValues)) === false) {
            return false;
        }
        foreach ($primaryKeys as $name => $value) {
            $id = static::getTableSchema()->columns[$name]->phpTypecast($value);
            $this->setAttribute($name, $id);
            $values[$name] = $id;
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }
        $updateValues = $this->getDatabaseFormattedValues($attributes);
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $updateValues[$lock] = $this->$lock + 1;
            $condition[$lock] = $this->$lock;
        }
        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $rows = static::updateAll($updateValues, $condition);

        if ($lock !== null && !$rows) {
            throw new StaleObjectException('The object being updated is outdated.');
        }

        if (isset($updateValues[$lock])) {
            $this->$lock = $updateValues[$lock];
        }

        $changedAttributes = [];
        $oldAttributes = $this->getOldAttributes();
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $oldAttributes[$name] = $value;
        }
        $this->afterSave(false, $changedAttributes);

        return $rows;
    }

    /**
     * @inheritDoc
     */
    public function afterFind()
    {
        $this->setAttributes($this->castAttributes(), false);

        parent::afterFind();
    }

    /**
     * @inheritDoc
     */
    public function afterRefresh()
    {
        $this->setAttributes($this->castAttributes(), false);

        parent::afterRefresh();
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if ($this->hasCast($name)) {
            $value = $this->castAttribute($name, $value);
        }

        parent::__set($name, $value);
    }

    /**
     * Decode the given float.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function fromFloat($value)
    {
        switch ((string) $value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float) $value;
        }
    }

    /**
     * Return a decimal as string.
     *
     * @param  float  $value
     * @param  int  $decimals
     * @return string
     */
    protected function asDecimal($value, $decimals)
    {
        return number_format($value, $decimals, '.', '');
    }

    /**
     * Get the type of cast for a model attribute.
     *
     * @param  string  $key
     * @return string
     */
    protected function getCastType($key)
    {
        if ($this->isDecimalCast($this->getCasts()[$key])) {
            return 'decimal';
        }

        return trim(strtolower($this->getCasts()[$key]));
    }

    /**
     * Determine if the cast type is a decimal cast.
     *
     * @param  string  $cast
     * @return bool
     */
    protected function isDecimalCast($cast)
    {
        return strncmp($cast, 'decimal:', 8) === 0;
    }

    /**
     * Determine whether a value is JSON castable for inbound manipulation.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isJsonCastable($key)
    {
        return $this->hasCast($key, ['array', 'json', 'object']);
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string  $value
     * @param  bool  $asObject
     * @return mixed
     */
    public function fromJson($value, $asObject = false)
    {
        return json_decode($value, ! $asObject);
    }

    /**
     * Cast the given attribute to JSON.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return string
     */
    protected function castAttributeAsJson($key, $value)
    {
        $value = $this->asJson($value);

        if ($value === false) {
            $message = json_last_error_msg();
            $modelClass = get_called_class();
            throw new InvalidArgumentException(
                "Unable to encode attribute [{$key}] for model [{$modelClass}] to JSON: {$message}."
            );
        }

        return $value;
    }

    /**
     * Encode the given value as JSON.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function asJson($value)
    {
        return json_encode($value);
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param  mixed  $value
     * @return Carbon
     */
    protected function asDate($value)
    {
        return $this->asDateTime($value)->startOfDay();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return Carbon
     */
    protected function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof \DateTimeInterface) {
            return Carbon::parse(
                $value->format('Y-m-d H:i:s.u'),
                $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if ($this->isStandardDateFormat($value)) {
            return Carbon::instance(Carbon::createFromFormat('Y-m-d', $value)->startOfDay());
        }

        $format = $this->getDateFormat();

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        try {
            $date = Carbon::createFromFormat($format, $value);
        } catch (\InvalidArgumentException $e) {
            $date = false;
        }

        return $date ?: Carbon::parse($value);
    }

    /**
     * Determine if the given value is a standard date format.
     *
     * @param  string  $value
     * @return bool
     */
    protected function isStandardDateFormat($value)
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d';
    }
}
