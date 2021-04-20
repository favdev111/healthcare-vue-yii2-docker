<?php

namespace modules\account\models;

use Yii;
use common\components\HtmlPurifier;

/**
 * This is the model class for table "{{%education_college}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $country
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Education[] $educations
 *
 */
class EducationCollege extends \yii\db\ActiveRecord
{
    const COUNTRY_USA = 1;
    const COUNTRY_CANADA = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%education_college}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEducations()
    {
        return $this->hasMany(Education::class, ['collegeId' => 'id']);
    }

    public function getFullName()
    {
        if ($this->country == self::COUNTRY_CANADA) {
            return $this->name . ' (' . $this->countryName . ')';
        }
        return $this->name;
    }

    public function getCountryName()
    {
        $countryNames = [
            self::COUNTRY_USA => 'USA',
            self::COUNTRY_CANADA => 'Canada',
        ];
        return $countryNames[$this->country] ?? $countryNames[$this->country];
    }
}
