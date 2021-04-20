<?php

namespace modules\task\controllers\console;

use common\models\AccountTerms;
use common\models\SignaturePdf;
use kartik\mpdf\Pdf;
use modules\account\Module;
use UrbanIndo\Yii2\Queue\Worker\Controller;

class SignatureController extends Controller
{
    public function actionCreatePdf(int $clientId, string $signaturePath, string $ip): bool
    {
        try {
            $pdf = new SignaturePdf();
            $pdf->clientId = $clientId;
            $pdf->signaturePath = $signaturePath;
            $pdf->ip = $ip;
            if (!file_exists($signaturePath)) {
                \Yii::error('Cannot find signature file ' . $signaturePath, 'terms');
                return false;
            }
            $pdf->prepareContent();
            if (!$pdf->validate()) {
                \Yii::error(json_encode($pdf->getFirstErrors()), 'terms');
                return false;
            }
            /**
             * @var Module $accountModule
             */
            $accountModule = \Yii::$app->getModule('account');
            $pathToSave = $accountModule->pathToSignedPdf . $pdf->fileName;
            $pdf->writeFile($pathToSave, 'Terms Of Use');
            //remove sign file
            unlink($signaturePath);
            /**
             * @var AccountTerms $term
             */
            //update term
            $term = AccountTerms::find()->andWhere(['accountId' => $clientId])->limit(1)->one();
            $term->isTermDocCreated = true;
            $term->save(false);
            return true;
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage() . "\n", $exception->getTraceAsString(), 'terms');
        }
        return false;
    }
}
