<?php

namespace modules\core\models;

use Yii;
use yii\helpers\Json;
use common\components\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%setting}}".
 *
 * @property int $id
 * @property string $section Section name to group config items
 * @property string $key Config key
 * @property int $isSimpleType (int, float, string, ...) if true, else is object or array
 * @property array $value Value in json format
 * @property string $description Config description
 * @property string $createdAt
 * @property string $updatedAt
 */
class Setting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['section', 'key', 'isSimpleType', 'value'], 'required'],
            [['isSimpleType'], 'boolean'],
            [['value'], 'safe'],
            [['section', 'key', 'description'], 'string', 'max' => 255],
            [['section', 'key'], 'unique', 'targetAttribute' => ['section', 'key']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'section' => 'Section',
            'key' => 'Key',
            'isSimpleType' => 'Is Simple Type',
            'value' => 'Value',
            'description' => 'Description',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * Return array of settings
     *
     * @return array
     */
    public function getSettings(): array
    {
        $result = [];
        $settings = static::find()->select(['isSimpleType', 'section', 'key', 'value'])->asArray()->all();

        foreach ($settings as $setting) {
            $section = $setting['section'];
            $key = $setting['key'];
            $value = Json::decode($setting['value']);

            if ($setting['isSimpleType']) {
                $value = $value['v'] ?? null;
            }

            $result[$section][$key] = $value;
        }
        return $result;
    }

    /**
     * Set setting
     *
     * @param string $section
     * @param string $key
     * @param mixed $value
     * @param bool $isSimpleType
     *
     * @return bool
     */
    public function setSetting(string $section, string $key, $value, bool $isSimpleType = true): bool
    {
        $model = static::findOne(['section' => $section, 'key' => $key]);
        if (empty($model)) {
            $model = new static();
        }

        if (is_array($value) || is_object($value)) {
            $isSimpleType = false;
        }

        $model->section = $section;
        $model->key = $key;
        $model->isSimpleType = $isSimpleType;

        if ($isSimpleType) {
            $model->value = ['v' => $value];
        } else {
            $model->value = $value;
        }

        return $model->save();
    }

    /**
     * Remove setting
     *
     * @param string $section
     * @param string $key
     *
     * @return bool
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function removeSetting(string $section, string $key): bool
    {
        $model = static::findOne(['section' => $section, 'key' => $key]);
        if (!empty($model)) {
            return (bool)$model->delete();
        }

        return false;
    }
}
