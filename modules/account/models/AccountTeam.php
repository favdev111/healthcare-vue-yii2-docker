<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use modules\account\models\query\AccountTeamQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%account_teams}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $teamId
 * @property string $createdAt
 * @property string $deletedAt
 * @property integer $createdBy
 *
 * @property Account $account
 * @property Team $team
 */
class AccountTeam extends \yii\db\ActiveRecord
{
    public static $accountClass = Account::class;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => false,
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            'softDeleteBehavior' => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'deletedAt' => time(),
                ],
                'replaceRegularDelete' => true
            ],
        ]);
    }

    public function search(array $params = []): ActiveDataProvider
    {
        $query = static::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ],
            ],
        ]);

        $this->load($params, '');
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['accountId' => $this->accountId]);
        $query->andFilterWhere(['teamId' => $this->teamId]);

        return $dataProvider;
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_teams}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId', 'teamId'], 'integer'],
            [['accountId'], 'unique', 'filter' => function ($query) {
                /**
                 * @var AccountTeamQuery $query
                 */
                $query->active();
            }
            ],
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => static::$accountClass, 'targetAttribute' => ['accountId' => 'id']],
            [['teamId'], 'exist', 'skipOnError' => true, 'targetClass' => Team::class, 'targetAttribute' => ['teamId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'teamId' => 'Team ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(static::$accountClass, ['id' => 'accountId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTeam(): ActiveQuery
    {
        return $this->hasOne(Team::class, ['id' => 'teamId']);
    }

    /**
     * @inheritdoc
     * @return AccountTeamQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AccountTeamQuery(get_called_class());
    }

    public static function getRoleByTeamId(int $teamId)
    {
        return Yii::$app->authManager->getRole(Team::getTeamRoleName(Team::getList()[$teamId]));
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (!$insert && !empty($changedAttributes['teamId'])) {
            $oldTeamRole = static::getRoleByTeamId($changedAttributes['teamId']);
            \Yii::$app->authManager->revoke($oldTeamRole, $this->accountId);
        }
        $newRole = static::getRoleByTeamId($this->teamId);
        \Yii::$app->authManager->assign($newRole, $this->accountId);
    }

    public function afterDelete()
    {
        $oldTeamRole = static::getRoleByTeamId($this->teamId);
        \Yii::$app->authManager->revoke($oldTeamRole, $this->accountId);
    }
}
