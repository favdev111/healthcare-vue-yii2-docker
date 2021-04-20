<?php

namespace console\components\queueJobs;

use common\models\SpecialistAgreementPdf;
use modules\account\Module;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

class CreateSpecialistAgreementPdfJob extends BaseObject implements RetryableJobInterface
{
    public $specialistId;
    public $date;

    public function execute($queue)
    {
        try {
            $pdf = new SpecialistAgreementPdf();
            $pdf->specialistId = $this->specialistId;
            $pdf->title = SpecialistAgreementPdf::FILE_NAME;
            $pdf->date = $this->date;
            if (!$pdf->validate(['specialistId', 'date'])) {
                \Yii::error(json_encode($pdf->getFirstErrors()), 'terms');
                return false;
            }

            $pdf->prepareContent();
            if (!$pdf->validate(['title', 'content', 'fileName'])) {
                \Yii::error(json_encode($pdf->getFirstErrors()), 'terms');
                return false;
            }

            /**
             * @var Module $accountModule
             */
            $accountModule = \Yii::$app->getModule('account');
            $pathToSave = $accountModule->pathToTutorAgreementPdf . $pdf->fileName;
            $pdf->writeFile($pathToSave, SpecialistAgreementPdf::FILE_NAME);
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage() . "\n" . $exception->getTraceAsString(), 'terms');
        }
        return true;
    }

    public function getTtr()
    {
        return 900;
    }

    public function canRetry($attempt, $error)
    {
        return false;
    }
}
