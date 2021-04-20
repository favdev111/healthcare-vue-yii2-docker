<?php

namespace modules\notification\controllers\api2;

use api2\components\RestController;
use common\helpers\Role;
use modules\notification\models\forms\api2\search\SearchNotification;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * Default controller for Notification model
 */
class DefaultController extends RestController
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

    /**
     * @return string|\yii\data\ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        return Yii::createObject(SearchNotification::class, [$this->currentAccount])
            ->search($this->request->get());
    }


    public function actionUnreadCount()
    {
        $count = Yii::createObject(SearchNotification::class, [$this->currentAccount])
            ->getUnreadCount();

        return [
            'count' => $count,
        ];
    }

    /**
     * @param $notificationId
     * @return void
     * @throws BadRequestHttpException
     */
    public function actionRead($notificationId)
    {
        $notification = $this->currentAccount
            ->getNotifications()
            ->andWhere(['notification.id' => $notificationId])
            ->unread()
            ->one();

        if (!$notification) {
            throw new BadRequestHttpException('Invalid notification data.');
        }

        $notification->markAsRead();
    }
}
