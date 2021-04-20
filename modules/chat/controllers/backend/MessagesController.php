<?php

namespace modules\chat\controllers\backend;

use backend\components\rbac\Rbac;
use modules\chat\models\ChatMessageSearch;
use Yii;
use backend\components\controllers\Controller;

/**
 * Messages controller
 */
class MessagesController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new ChatMessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
