<?php

namespace api2\components;

use modules\account\models\Account;

/**
 * Class ActiveController
 * @package api2\components
 *
 * @property-read \modules\account\models\api2\Account $currentAccount
 * @property-read \common\components\Request $request
 */
class ActiveController extends \yii\rest\ActiveController
{
    use ControllerTrait;
    use AuthControllerTrait;

    public $serializer = Serializer::class;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            $this->authBehaviors(),
            $this->contentNegoriatorBehaviors(),
            $this->otherBehaviors()
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->validateDeviceParams();

        return true;
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['options']);
        return $actions;
    }

    /**
     * @return Account
     */
    public function getCurrentAccount(): Account
    {
        return \Yii::$app->user->identity;
    }
}
