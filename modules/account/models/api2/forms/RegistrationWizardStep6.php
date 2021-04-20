<?php

namespace modules\account\models\api2\forms;

use api2\components\models\forms\ApiBaseForm;
use common\components\HtmlPurifier;
use modules\account\models\ar\Language;
use yii\base\Model;

class RegistrationWizardStep6 extends ApiBaseForm
{
    public $file;
    public $title;
    public $description;
    public $languages;

    public function rules()
    {
        return [
            [['file'], 'required'],
            [
                ['file'],
                'image',
                'extensions' => ['png', 'jpg', 'jpeg', 'gif'],
                'minWidth' => 600,
                'maxWidth' => 8000,
                'minHeight' => 600,
                'maxHeight' => 8000,
            ],
            [['title', 'description'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
            }
            ],
            [['title', 'description', 'languages'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 20000],
            [
                ['languages'],
                'languagesValidator'
            ],
        ];
    }

    public function scenarios()
    {
        return array_merge(
            parent::scenarios(),
            [
                'upload' => ['file'],
                'default' => ['title', 'description', 'languages'],
            ]
        );
    }

    public function languagesValidator($attribute, $params, $validator)
    {
        $attributeValue = $this->$attribute;

        foreach ($attributeValue as $item) {
            $stateId = $item['languageId'];
            if (empty($stateId)) {
                $this->addError($attribute, 'Language is required');
            }

            if (!is_numeric((int)$stateId)) {
                $this->addError($attribute, 'Incorrect Language provided');
            }

            $isExists = Language::find()->andWhere(['id' => $stateId])->exists();
            if (!$isExists) {
                $this->addError($attribute, 'Incorrect language provided');
            }
        }
    }
}
