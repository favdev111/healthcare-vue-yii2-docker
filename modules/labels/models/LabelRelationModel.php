<?php

namespace modules\labels\models;

use common\components\behaviors\CreatedUpdatedBehavior;
use common\components\behaviors\TimestampBehavior;
use modules\account\models\Account;
use modules\account\models\EmployeeClient;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class LabelRelationModel
 * @package modules\labels\models
 * @property int $labelId
 * @property int $itemId
 * @property int $createdBy
 * @property string $description
 * @property int $updatedBy
 * @property string $relatedTo
 */
class LabelRelationModel extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%labels_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['labelId', 'itemId'], 'required'],
            ['labelId', 'exist', 'targetClass' => Labels::class, 'targetAttribute' => ['labelId' => 'id']],
            [['labelId', 'itemId'], 'integer'],
            [
                'itemId',
                'exist',
                'targetClass' => Account::class,
                'targetAttribute' => ['itemId' => 'id'],
                'message' => 'Client doesn\'t exist.'
            ],
            [
                'itemId',
                'exist',
                'targetClass' => EmployeeClient::class,
                'targetAttribute' => ['itemId' => 'clientId'],
                'message' => 'Task doesn\'t exist. Please refresh the page'
            ],
            [
                'itemId',
                function ($attribute) {
                    $user = Yii::$app->user->identity;
                    if ($user->isCompanyEmployee()) {
                        $exist = Account::find()
                            ->andWhere(['id' => $this->itemId])
                            ->exists();
                        if (!$exist) {
                            $this->addError($attribute, 'Client not related to this company');
                        }
                    }
                },
            ],
            [
                'itemId',
                'unique',
                'targetAttribute' => ['itemId'],
                'message' => 'This task already has a label assigned. Please refresh the page',
            ],
            ['description', 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'labelId' => 'Label ID',
            'itemId' => 'Item ID',
            'description' => 'Description',
        ];
    }


    /**
     * @return ActiveQuery
     */
    public function getLabel(): ActiveQuery
    {
        return $this->hasOne(Labels::class, ['id' => 'labelId']);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            [
                'class' => CreatedUpdatedBehavior::class,
            ],
        ];
    }

    /**
     * @param int $clientId
     * @return bool
     */
    public function canAssign(int $clientId): bool
    {
        $user = Yii::$app->user->identity;
        if ($user->isCompanyEmployee()) {
            $clientEmployee = EmployeeClient::find()
                ->andWhere(['employeeId' => $user->getId()])
                ->andWhere(['clientId' => $clientId])
                ->exists();
            if ($clientEmployee) {
                return true;
            }
        }
        if ($user->isCrmAdmin()) {
            return true;
        }
        return false;
    }
}
