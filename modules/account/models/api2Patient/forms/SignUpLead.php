<?php

namespace modules\account\models\api2Patient\forms;

use common\components\HtmlPurifier;
use common\components\validators\NameStringValidator;
use modules\account\models\api2\Symptom;
use udokmeci\yii2PhoneValidator\PhoneValidator;

class SignUpLead extends \yii\base\Model
{
    public $email;
    public $name;
    public $phone_number;
    public $relations;

    protected $typeToClass = [
        'symptom' => Symptom::class,
    ];

    public function rules()
    {
        return [
            [['name'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
            }
            ],
            [['email', 'phone_number', 'name', 'relations'], 'required'],
            [['email', 'phone_number', 'name'], 'string'],
            [['email'], 'email'],
            [
                ['name'],
                NameStringValidator::class
            ],
            [['name'], 'string', 'max' => 255],
            ['phone_number', PhoneValidator::class, 'country' => 'US', 'format' => false],
            [['relations'], 'relationsValidator'],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function relationsValidator($attribute, $params, $validator)
    {
        $attributeValue = $this->$attribute;

        foreach ($attributeValue as $item) {
            $id = $item['id'] ?? null;
            $type = $item['type'] ?? null;
            if (!$id || !$type) {
                $this->addError($attribute, 'Attributes `id` and `type` are required.');
                return;
            }

            if (!array_key_exists($type, $this->typeToClass)) {
                $this->addError($attribute, 'Attributes id and type are required.');
            }

            $class = $this->typeToClass[$type];

            $isExists = $class::find()->andWhere(['id' => $id])->exists();
            if (!$isExists) {
                $this->addError($attribute, 'Incorrect ' . $type . ' provided.');
            }
        }
    }

    public function getRelationModels()
    {
        $models = [];
        foreach ($this->relations as $relation) {
            $type = $relation['type'];
            $class = $this->typeToClass[$type];
            $models[] = $class::findOne($relation['id']);
        }

        return $models;
    }
}
