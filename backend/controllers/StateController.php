<?php

namespace backend\controllers;

use backend\actions\AjaxSearchAction;
use backend\components\controllers\Controller;

/**
 * Class StateController
 * @package backend\controllers
 */
class StateController extends Controller
{
    /**
     * @return \string[][]
     */
    public function actions()
    {
        return [
            'ajax-search' => [
                'class' => AjaxSearchAction::class,
                'tableName' => 'state',
            ],
        ];
    }
}
