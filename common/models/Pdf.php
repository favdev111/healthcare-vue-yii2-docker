<?php

namespace common\models;

use Yii;
use yii\base\Model;
use kartik\mpdf\Pdf as KPdf;

class Pdf extends Model
{
    public $content;
    public $fileName;
    public $title;
    public $redirectUrl = '/';

    public function rules()
    {
        return [
            [['content', 'fileName'], 'required'],
            [['content', 'fileName', 'title'], 'string']
        ];
    }

    /**
     * @return KPdf
     */
    public function getPdf()
    {
        return new KPdf([
            'mode' => KPdf::MODE_CORE,
            'format' => KPdf::FORMAT_A4,
            'orientation' => KPdf::ORIENT_PORTRAIT,
            'destination' => KPdf::DEST_STRING,
            'content' => $this->content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'options' => ['title' => $this->title],
        ]);
    }

    public function writeFile(string $pathToSave, string $title)
    {
        $preparedPdf = new KPDF([
            'mode' => KPDF::MODE_CORE,
            'format' => KPDF::FORMAT_A4,
            'orientation' => KPDF::ORIENT_PORTRAIT,
            'destination' => KPDF::DEST_FILE,
            'filename' => $pathToSave,
            'content' => $this->content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'methods' => [
                'SetTitle' => [$title],
            ],
        ]);
        $preparedPdf->render();
    }

    public function getPdfAsResponse()
    {
        $pdf = $this->getPdf();

        if (!$this->validate() || $this->hasErrors()) {
            if (Yii::$app->isApiApp()) {
                return $this;
            } else {
                foreach ($this->getErrors() as $field => $errArray) {
                    foreach ($errArray as $message) {
                        Yii::$app->session->setFlash('error', $message);
                    }
                }
                return Yii::$app->controller->redirect($this->redirectUrl);
            }
        }

        return Yii::$app->response->sendContentAsFile(
            $pdf->render(),
            $this->fileName,
            [
                'mimeType' => 'application/pdf',
                'inline' => false,
            ]
        );
    }
}
