<?php

namespace modules\account\models;

use modules\account\models\Account;
use Yii;
use yii\base\Model;

class ModelTutorScore extends Model
{
    public $type = TutorScoreSettings::TYPE_CONTENT_PROFILE;
    public $settings;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['settings'], 'required'],
            ['settings', 'validateMultipleScore']
        ];
    }

    public function init()
    {
        $query = TutorScoreSettings::find();

        $this->settings = $query->andFilterWhere(['type' => $this->type])->indexBy('id')->all();
    }

    public function load($data, $formName = null)
    {
        if (Model::loadMultiple($this->settings, $data)) {
            foreach ($this->settings as $setting) {
                $key = $setting['key'];

                if (mb_strpos($key, '+')) {
                    $values = explode('+', $key);
                    $setting->min = (double)$values[0];
                    $setting->max = (double)$values[0];
                } elseif (mb_strpos($key, '-')) {
                    $values = explode('-', $key);
                    $setting->min = (double)$values[0];
                    $setting->max = (double)$values[1];
                } else {
                    $value = (double)$key;
                    $setting->min = $value;
                    $setting->max = $value;
                }
            }
            return true;
        }
        return false;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (Model::validateMultiple($this->settings)) {
            return parent::validate($attributeNames, $clearErrors);
        }
        return false;
    }

    public function save()
    {
        foreach ($this->settings as $setting) {
            $setting->save(false);
        }
    }

    public function validateMultipleScore()
    {
        $settings = $this->settings;
        \yii\helpers\ArrayHelper::multisort($settings, 'min');
        $max = 0;
        foreach ($settings as $setting) {
            if ($setting->min <= $max && $max != 0) {
                $this->addError('settings', 'The values intersect');
                return false;
            }
            if ($setting->min <= $setting->max) {
                $max = $setting->max;
                continue;
            }
            $this->addError('settings', 'The values intersect');
            return false;
        }
        return true;
    }
}
