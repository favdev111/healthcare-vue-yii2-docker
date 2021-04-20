<?php

use backend\components\rbac\Rbac;

/**
 * Class m170915_084226_rbac_data
 */
class m170915_084226_rbac_data extends \yii\db\Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        Rbac::initialization();
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        Yii::$app->authManager->removeAll();
    }
}
