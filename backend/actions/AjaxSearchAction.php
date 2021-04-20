<?php

namespace backend\actions;

use common\models\health\HealthTest;
use Yii;
use yii\base\Action;
use yii\db\Query;

/**
 * Class AjaxSearchAction
 * @package backend\actions
 */
class AjaxSearchAction extends Action
{
    /**
     * @var string
     */
    public string $tableName;
    /**
     * @var int
     */
    public int $limitItems = 20;

    public function init()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        parent::init();
    }

    /**
     * @param null $q
     * @return \string[][]
     * @throws \yii\db\Exception
     */
    public function run($q)
    {
        $data = (new Query())
            ->select('id, name AS text')
            ->from($this->tableName)
            ->where(['like', 'name', $q])
            ->limit($this->limitItems)
            ->createCommand()
            ->queryAll();

        return [
            'results' => array_values($data),
        ];
    }
}
