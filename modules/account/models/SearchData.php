<?php

namespace modules\account\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use common\components\HtmlPurifier;

/**
 * This is the model class for table "{{%search_data}}".
 *
 * @property integer $id
 * @property string $search
 * @property string $page
 * @property integer $who
 * @property string $zipCode
 * @property string $createdAt
 * @property string $updatedAt
 */
class SearchData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%search_data}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['search'], 'required'],
            [['search', 'page'], 'string', 'max' => 255],
            [['search', 'page', 'zipCode'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'who',
                'updatedByAttribute' => 'who',
            ],
        ];
    }

    public function getWhoIs()
    {
        if ($this->account) {
            return $this->account->fullName;
        }
        return 'Guest';
    }

    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'who']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'search' => 'Search',
            'page' => 'Page',
            'who' => 'Who',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }
}
