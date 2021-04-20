<?php

namespace modules\chat\controllers\backend;

use backend\components\rbac\Rbac;
use modules\account\models\Account;
use modules\account\models\AccountWithDeleted;
use modules\chat\helpers\ChatDataProvider;
use modules\chat\models\Chat;
use modules\chat\models\ChatSearch;
use modules\chat\Module;
use Yii;
use backend\components\controllers\Controller;
use yii\web\NotFoundHttpException;

/**
 * JobController implements the CRUD actions for Job model.
 */
class DefaultController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        $behaviors['access']['rules'][] = [
            'actions' => ['list', 'messages'], // add all actions to take guest to login page
            'allow' => true,
            'roles' => [Rbac::PERMISSION_VIEW_ALL],
        ];

        return $behaviors;
    }

    /**
     * Lists all Dialogs for provided user models.
     * @param $id integer User ID
     * @return mixed
     */
    public function actionList($id)
    {
        $user = $this->findAccountModel($id);

        $dataProvider = new ChatDataProvider([
            'user' => $user,
            'type' => ChatDataProvider::TYPE_DIALOGS,
        ]);
        return $this->render('list', [
            'user' => $user,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionApprove($id)
    {
        $model = Chat::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        $model->status = Chat::STATUS_ACTIVE;
        if ($model->save()) {
            Yii::$app->session->addFlash('success', 'Chat Account approved successfully');
        } else {
            Yii::$app->session->addFlash('error', 'Failed to approve Chat Account');
        }
        return $this->redirect(['/chat/default/suspicious-users']);
    }

    public function actionHold($id)
    {
        $model = Chat::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        $model->status = Chat::STATUS_HOLD;
        $model->statusReason = Chat::STATUS_REASON_SPAM;
        if ($model->save()) {
            Yii::$app->session->addFlash('success', 'Chat Account put on hold successfully');
        } else {
            Yii::$app->session->addFlash('error', 'Failed to put Chat Account on hold');
        }
        return $this->redirect(['/chat/default/suspicious-users']);
    }

    /**
     * Lists all Suspicious Chat users for provided user models.
     * @return mixed
     */
    public function actionSuspiciousUsers()
    {
        $searchModel = new ChatSearch([
            'onlySuspicious' => true,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('suspiciousUsers', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Messages for provided Dialog.
     * @param $dialogId integer Dialog ID
     * @param $userId integer User ID
     * @return mixed
     */
    public function actionMessages($dialogId, $userId)
    {
        $user = $this->findAccountModel($userId);
        /** @var Module $chat */
        $chat = Yii::$app->getModule('chat');
        $dialogs = $chat->getDialog($dialogId, $user);
        $dialog = $dialogs['items'][0] ?? $dialogs['items'][0] ?? null;
        if (!$dialog) {
            Yii::$app->session->addFlash('error', 'No such chat found');
            return $this->redirect('/backend/');
        }
        $withUser = \modules\chat\models\Chat::getOpponentAccount(
            $dialog['occupants_ids'],
            $user->id,
            false,
            true
        );

        $dataProvider = new ChatDataProvider([
            'user' => $user,
            'dialogId' => $dialogId,
            'type' => ChatDataProvider::TYPE_MESSAGES,
        ]);
        return $this->render('messages', [
            'dialog' => $dialog,
            'user' => $user,
            'withUser' => $withUser,
            'dataProvider' => $dataProvider,
        ]);
    }

    protected function findAccountModel($id)
    {
        $account = AccountWithDeleted::findOne($id);
        if (!$account) {
            Yii::$app->session->addFlash('error', 'No such user found');
            return $this->redirect('/backend/');
        }

        return $account;
    }
}
