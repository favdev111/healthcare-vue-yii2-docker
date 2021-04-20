<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\ClientChild]].
 *
 * @see \common\models\ClientChild
 */
class ClientChildQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\ClientChild[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\ClientChild|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function ofProfile($id)
    {
        return $this->andWhere(['clientProfileId' => $id]);
    }
}
