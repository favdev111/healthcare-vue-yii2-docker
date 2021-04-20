<?php

namespace modules\account\models;

use modules\account\models\query\TeamQuery as TeamQuery;
use Yii;

/**
 * This is the model class for table "{{%teams}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $isActive
 *
 * @property array $list
 */
class Team extends \yii\db\ActiveRecord
{
    const OPS_TEAM_ID = 2;
    const OPS_TEAM_LABEL = 'OPS';
    const SALES_TEAM_LABEL = 'Sales';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%teams}}';
    }

    public static function getTeamRoleName(string $teamName): string
    {
        return 'team_' . $teamName;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['isActive'], 'boolean'],
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
            'isActive' => 'Is Active',
        ];
    }

    /**
     * @inheritdoc
     * @return TeamQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TeamQuery(get_called_class());
    }

    /**
     * @return array - list of team indexed by id
     */
    public static function getList(): array
    {
        $result = static::find()->select(['id','name'])->indexBy('id')->asArray()->all();
        foreach ($result as &$item) {
            $item = $item['name'];
        }
        return $result;
    }
}
