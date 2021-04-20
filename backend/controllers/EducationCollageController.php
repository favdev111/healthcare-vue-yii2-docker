<?php

namespace backend\controllers;

use backend\actions\AjaxSearchAction;
use backend\components\controllers\Controller;

/**
 * Class EducationCollageController
 * @package backend\controllers
 */
class EducationCollageController extends Controller
{
    /**
     * @return \string[][]
     */
    public function actions()
    {
        return [
            'ajax-search' => [
                'class' => AjaxSearchAction::class,
                'tableName' => 'education_college',
            ],
        ];
    }
}
