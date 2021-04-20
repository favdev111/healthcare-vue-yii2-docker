<?php

namespace modules\account\controllers\api2Patient;

use api2\components\RestController;
use modules\account\models\api2Patient\Notes;
use common\helpers\Role;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Class NoteController
 * @package modules\account\controllers\api2Patient
 */
class NoteController extends RestController
{
    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_PATIENT],
            ],
        ];
    }
    public function actions()
    {
       
        $account = $this->module->modelStatic('Account');
        return [
            'signin' => [
                'class' => LoginAction::class,
                'roles' => [Role::ROLE_SPECIALIST, Role::ROLE_PATIENT],
                'accountQuery' => $account::find(),
            ],
        ];
    }

    /**
     * @param $id
     * @return HealthProfile
     * @throws ForbiddenHttpException
     */
    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        $form = new Notes();
        $form->load($data, '');
        if (!$form->validate()) {
            return $form;
        }
        $form->load($data, '');
        $form->save(false);
        return $form;
    }
    public function actionNotesList()
    {
        $accountId = Yii::$app->request->get('accountId');
        return Notes::find()
            ->andWhere(['accountId' => $accountId])
            ->all();
    }
}
