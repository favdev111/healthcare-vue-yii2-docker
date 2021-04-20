<?php

namespace modules\chat\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use modules\chat\actions\ChatMarkReadAction;
use modules\chat\actions\ChatSendAction;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;

/**
 * Default controller
 */
class DefaultController extends \api\components\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'authenticator' => [
                    'class' => HttpBearerAuth::class,
                    'except' => [
                        'options',
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'except' => [
                        'options'
                    ],
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => [
                                Rbac::PERMISSION_BASE_B2B_PERMISSIONS,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
            'send' => [
                'class' => ChatSendAction::class,
            ],
            'mark-read' => [
                'class' => ChatMarkReadAction::class,
            ],
        ];
    }

    /**
     * @OA\Post(
     *     path="/chats/send/{chatUserId}/",
     *     tags={"chats"},
     *     summary="Send message",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Recipient account ID",
     *         in="path",
     *         name="chatUserId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="fromChatUserId",
     *                 type="string",
     *                 description="Message from (chatUserId)"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={ "chat", "image" },
     *                 description="Message type"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Message text"
     *             ),
     *             @OA\Property(
     *                 property="jobId",
     *                 type="integer",
     *                 description="Job id for this message"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */

    /**
     * @OA\Post(
     *     path="/chats/mark-read/{messageId}/{dialogId}/",
     *     tags={"chats"},
     *     summary="Mark message as read",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Message ID",
     *         in="path",
     *         name="messageId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Dialog ID",
     *         in="path",
     *         name="dialogId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
}
