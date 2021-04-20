<?php

namespace backend\controllers;

use modules\core\models\ApiLogRequest;
use modules\core\models\search\ApiLogRequestSearch;
use Yii;
use backend\components\controllers\Controller;

/**
 * ChangeLogController implements the CRUD actions for ChangeLog model.
 */
class ApiLogRequestController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new ApiLogRequestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * View log request action
     *
     * @param $key
     * @param $id
     * @param $type
     *
     * @return string
     */
    public function actionAjaxViewLogRequest($id, $type)
    {
        $logRequest = ApiLogRequest::findOne($id);

        return empty($logRequest->{$type}) ? '' : nl2br(str_replace(['\\n', ' '], ["\n", '&nbsp;'], gzinflate($logRequest->{$type})));
    }
}
