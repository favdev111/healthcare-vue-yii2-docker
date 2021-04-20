<?php

namespace modules\chat\models;

use Yii;

/**
 * This is the model class for table "{{%chat_restriction}}".
 *
 * @property integer $id
 * @property integer $type
 *
 * @property mixed $data
 * @property string $dataText
 * @property string $typeText
 */
class ChatRestriction extends \yii\db\ActiveRecord
{
    protected $decodedValue = null;

    const CACHE_KEY = 'chatRestriction';
    const TYPE_MAIL_DOMAINS = 1;

    public static $types = [
        self::TYPE_MAIL_DOMAINS => 'Mail domains',
    ];

    public function getTypeText()
    {
        return static::$types[$this->type] ?? $this->type;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%chat_restriction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type'], 'integer'],
            [['data'], 'safe'],
            [['dataText'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'value' => 'Value',
        ];
    }

    public function setData($data)
    {
        $this->value = json_encode($data);
    }

    public function getData($reload = false)
    {
        if ($reload || !$this->decodedValue) {
            $this->decodedValue = json_decode($this->value, true);
        }
        return $this->decodedValue;
    }

    public function getDataText($reload = false)
    {
        return implode(', ', $this->getData($reload));
    }

    public function setDataText($dataText)
    {
        $dataList = array_map(function ($item) {
            return trim($item);
        }, array_filter(explode(',', $dataText), function ($value) {
            return !empty($value);
        }));
        $this->setData($dataList);
    }
}
