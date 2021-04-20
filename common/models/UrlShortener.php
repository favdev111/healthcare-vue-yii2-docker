<?php

namespace common\models;

use common\helpers\UrlFrontend;
use Yii;

/**
 * This is the model class for table "{{%url_shortener}}".
 *
 * @property integer $id
 * @property string $code
 * @property array $data
 * @property string $usedAt
 * @property string $createdAt
 *
 * @property-read string url
 * @property-read string redirectUrl
 */
class UrlShortener extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%url_shortener}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => \common\components\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => null,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data'], 'required'],
            [['data'], function ($attribute) {
                $value = $this->$attribute;
                if (!is_array($value)) {
                    $this->addError($attribute, 'Must be an array');
                }

                if (empty($value['route'])) {
                    $this->addError($attribute, 'Must have a route');
                }
            }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            do {
                $code = preg_replace('/[^A-Za-z0-9]/', '', Yii::$app->security->generateRandomString());
                $code = substr($code, 0, 7);
            } while (static::find()->andWhere(['code' => $code])->exists());

            $this->code = $code;
        }

        return true;
    }

    /**
     * @return string Shortener Url
     */
    public function getUrl()
    {
        return UrlFrontend::to(['/site/shortener', 'code' => $this->code], true);
    }

    /**
     * @return string Destination Url
     */
    public function getRedirectUrl()
    {
        return UrlFrontend::to($this->data['route']);
    }
}
