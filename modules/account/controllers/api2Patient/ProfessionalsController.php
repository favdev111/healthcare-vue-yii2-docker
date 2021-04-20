<?php

namespace modules\account\controllers\api2Patient;

use common\components\UploadedFile;
use common\helpers\AccountStatusHelper;
use common\helpers\QueueHelper;
use modules\account\models\AccountRating;
use modules\account\models\api2\Account;
use modules\account\models\api2\AccountEducation;
use modules\account\models\api2\AccountLicenceState;
use modules\account\models\api2\AccountReward;
use modules\account\models\api2\AccountTelehealthState;
use modules\account\models\api2\forms\RegistrationWizardStep1;
use common\helpers\Role;
use modules\account\models\api2\forms\RegistrationWizardStep2;
use modules\account\models\api2\forms\RegistrationWizardStep3;
use modules\account\models\api2\forms\RegistrationWizardStep4;
use modules\account\models\api2\forms\RegistrationWizardStep5;
use modules\account\models\api2\AccountRate;
use modules\account\models\api2\forms\RegistrationWizardStep6;
use modules\account\models\ar\AccountInsuranceCompany;
use modules\account\models\ar\AccountLanguage;
use modules\account\models\backend\AccountProfessionalSearch;
use modules\account\responses\AccountResponse;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Default controller for Account actions
 */
class ProfessionalsController extends \api2\components\RestController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['create', 'update'],
                'rules' => [
                    // allow authenticated users
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // everything else is denied
                ],
            ],
        ];
    }

    /**
     * API request for /api/professionals
     */
    public function actionIndex()
    {
        $payload = Yii::$app->request->get();
        $params = ['page' => 0, 'pageSize' => 10, 'status' => [AccountStatusHelper::STATUS_ACTIVE]];

        foreach ($payload as $key => $value) {
            switch ($key) {
                case 'role':
                    $params['professionalTypeId'] = $value;
                    break;
                case 'gender':
                    $params['gender'] = $value;
                    break;
                case 'speciality':
                    $params['doctorTypeId'] = $value;
                    break;
                case 'experience':
                    $params['educations'] = $value;
                    break;
                case 'minRange':
                    $params['minRange'] = $value;
                    break;
                case 'maxRange':
                    $params['maxRange'] = $value;
                    break;
                case 'states':
                    $params['telehealthStates'] = $value;
                    break;
                case 'duration':
                    $params['duration'] = $value;
                    break;
                case 'page':
                    $params['page'] = $value;
                    break;
                case 'pageSize':
                    $params['pageSize'] = $value;
                    break;
                case 'minRating':
                    $params['minRating'] = $value;
                    break;
                case 'maxRating':
                    $params['maxRating'] = $value;
                    break;
                default:
            }
        }
        $searchModel = new AccountProfessionalSearch();
        $dataProvider = $searchModel->search($params);
        $totalCount = $dataProvider->getTotalCount();

        return ['data' => $dataProvider, 'total' => $totalCount];
    }

    /**
     * API request for /api/professionals/:id
     */
    /**
     * @OA\Get (
     *     path="/patient/professionals/{id}",
     *     tags={"Professional Profile"},
     *     summary="Get professional profile info",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Professional profile id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful result",
     *         @OA\JsonContent(ref="#/components/schemas/ProfessionalProfileResponse")
     *     )
     * )
     */
    public function actionProfessional(int $id)
    {
        $model = Account::findOne($id);

        if (($model) !== null) {
            return [
                'id' => $model->id,
                'publicId' => $model->publicId,
                'hasPhoto' => $model->hasPhoto,
                'email' => $model->email,
                'profile' => $model->profile,
                'rate' => $model->rate,
                'rating' => $model->rating,
                'reviews' => $model->reviews,
                'languages' => $model->languages,
                'educations' => $model->educations,
                'certifications' => $model->certifications,
                'healthTests' => $model->healthTests,
                'symptoms' => $model->symptoms,
                'medicalConditions' => $model->medicalConditions,
                'healthGoals' => $model->healthGoals,
                'autoimmuneDiseases' => $model->autoimmuneDiseases,
            ];
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * API request for /api/professionals/ratings
     */
    public function actionRatings()
    {
        $payload = Yii::$app->request->post('rating');

        if (!$payload) {
            return [];
        }

        $searchModel = new AccountProfessionalSearch();
        $result = [];

        foreach ($payload as $key => $values) {
            $dataProvider = $searchModel->ratings(array_merge($values, ['status' => [AccountStatusHelper::STATUS_ACTIVE]]));
            $totalCount = $dataProvider->getTotalCount();
            $result[] = ['id' => $values['id'], 'count' => $totalCount];
        }

        return $result;
    }
}
