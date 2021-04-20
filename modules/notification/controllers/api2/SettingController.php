<?php

namespace modules\notification\controllers\api2;

use api2\components\RestController;
use common\helpers\Role;
use modules\notification\models\entities\api2\setting\NotificationType;
use modules\notification\models\forms\api2\setting\SetupSettingForm;
use Yii;

/**
 * Class SettingController
 * @package modules\notification\controllers\api2
 */
class SettingController extends RestController
{
    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_SPECIALIST],
            ],
        ];
    }

    /**
     * @return string|\yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        return NotificationType::find()->all();
    }

    /**
     * @return SetupSettingForm|object|void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $form = Yii::createObject(SetupSettingForm::class, [$this->currentAccount]);

        $form->load($this->request->post());

        if ($form->setup()) {
            return;
        }

        return $form;
    }
}
