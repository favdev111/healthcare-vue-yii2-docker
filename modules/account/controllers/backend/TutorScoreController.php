<?php

namespace modules\account\controllers\backend;

use backend\components\rbac\Rbac;
use common\helpers\Automatch;
use common\models\search\SentNewJobNotificationSearch;
use modules\account\models\ModelTutorScore;
use modules\account\models\TutorScoreSettings;
use Yii;
use backend\components\controllers\Controller;
use yii\helpers\ArrayHelper;

class TutorScoreController extends Controller
{
    public const TYPE_SCORE_STATISTIC = 'statistic';
    public const TYPE_SCORE_APPLY_BONUS = 'applyBonus';
    /**
     * Lists all TutorScoreSettings models.
     * @return mixed
     */
    public function actionIndex()
    {
        $type = Yii::$app->request->get('type', TutorScoreSettings::TYPE_CONTENT_PROFILE);
        if ($type == static::TYPE_SCORE_STATISTIC) {
            $search = new SentNewJobNotificationSearch();
            $statisticProvider = $search->search(Yii::$app->request->get('SentNewJobNotificationSearch'));
        } elseif ($type == static::TYPE_SCORE_APPLY_BONUS) {
            if (Yii::$app->request->isPost) {
                foreach (Yii::$app->request->post() as $k => $v) {
                    if (in_array($k, Automatch::$applyBonusKeys)) {
                        Automatch::setBonusPointsValue($k, (int)$v);
                    }
                }
                Yii::$app->settings->invalidateCache();
            }
        } else {
            $tutorScores = new ModelTutorScore(['type' => $type]);
        }
        if (Yii::$app->request->isPost && isset($tutorScores)) {
            if (Yii::$app->user->can(Rbac::PERMISSION_BACKEND_FULL_MANAGEMENT) === false) {
                Yii::$app->session->setFlash('error', 'You are not allowed to perform this action.');
                return $this->redirect(Yii::$app->request->referrer);
            }

            $settingsList = TutorScoreSettings::find()->byType((int)$type)->all();

            $oldIDs = ArrayHelper::map($settingsList, 'id', 'id');
            $settingsList = TutorScoreSettings::createMultiple($settingsList);
            TutorScoreSettings::loadMultiple($settingsList, Yii::$app->request->post());
            $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($settingsList, 'id', 'id')));

            // validate all models
            $valid = TutorScoreSettings::validateMultiple($settingsList);

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $flag = true;
                    if (! empty($deletedIDs)) {
                        TutorScoreSettings::deleteAll(['id' => $deletedIDs]);
                    }
                    foreach ($settingsList as $settingModel) {
                        $settingModel->type = (int)$type;
                        if (! ($flag = $settingModel->save(false))) {
                            $transaction->rollBack();
                            break;
                        }
                    }
                    if ($flag) {
                        $transaction->commit();
                    }
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash("error", $e->getMessage());
                }
            } else {
                Yii::$app->session->setFlash("error", $tutorScores->getFirstError('settings'));
            }
        }

        return $this->render('index', [
            'settings' => array_values(isset($settingsList) && sizeof($settingsList) > 0
                ? $settingsList
                : (isset($tutorScores) && $tutorScores->settings && sizeof($tutorScores->settings) > 0
                    ? $tutorScores->settings
                    : [new TutorScoreSettings()])),
            'type' => $type,
            'statisticProvider' => $statisticProvider ?? null,
            'search' => $search ?? null
        ]);
    }
}
