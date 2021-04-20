<?php

namespace api2\components;

use modules\account\models\Account;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;

/**
 * Class RestController
 * @package api2\components
 *
 * @property-read \modules\account\models\api2\Account $currentAccount
 * @property-read \yii\web\Request $request
 */
class RestController extends Controller
{
    use ControllerTrait;
    use AuthControllerTrait;

    public $serializer = Serializer::class;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
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
        if (! parent::beforeAction($action)) {
            return false;
        }

        $this->validateDeviceParams();

        return true;
    }

    /**
     * @return Account
     */
    public function getCurrentAccount(): Account
    {
        return Yii::$app->user->identity;
    }

    public function setNoContent(): void
    {
        $this->response->setStatusCode(204);
    }
}
