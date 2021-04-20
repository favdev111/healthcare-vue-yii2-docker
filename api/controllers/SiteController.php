<?php

/**
 * @OA\OpenApi(
 *     schemes={"https", "http"},
 *     basePath="/api",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Heytutor API",
 *         @OA\Contact(
 *             email="hello@icemint.co"
 *         )
 *     )
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityDefinition="Bearer",
 *   type="apiKey",
 *   name="Authorization",
 *   in="header"
 * )
 */

namespace api\controllers;

use Yii;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return '';
    }
}
