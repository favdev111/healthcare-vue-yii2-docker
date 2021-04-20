<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;
use yii\behaviors\SluggableBehavior;
use modules\account\models\Profile;

/**
 * Class ListPatient
 * @property int $id
 * @property string $email
 * @property int $roleId
 * @package modules\account\models\ar
 * @property Profile $accountProfile
 */

class ListPatient extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%account}}';
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'role' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'roleId',
                'ensureUnique' => false,
                'value' => function () {
                    if ($this->role) {
                        return $this->role;
                    }
                },
            ],
        ];
    }
    /**
     * Gets query for [[Profile]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\ProfileQuery
     */
    public function getAccountProfile()
    {
        return $this->hasOne(Profile::class, ['accountId' => 'id']);
    }
    /**
     * {@inheritdoc}
     * @return \common\models\query\ListPatientQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ListPatientQuery(get_called_class());
    }
}
