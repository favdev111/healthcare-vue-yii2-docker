<?php

namespace common\models;

use common\models\form\SignatureForm;
use kartik\mpdf\Pdf as KPDF;
use modules\account\models\Account;

class SignaturePdf extends Pdf
{
    const TEMPLATE_PATH_ALIAS = '@themes/basic/modules/account/views/common/signature/terms_pdf';
    public $clientId;
    public $signaturePath;
    public $ip;
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['clientId', 'signaturePath'], 'required'],
            [['clientId'], 'exist', 'targetClass' => Account::class, 'targetAttribute' => 'id'],
            [['signaturePath'], 'string'],
        ]);
    }

    public function prepareContent()
    {
        $form = new SignatureForm();
        $form->clientId = $this->clientId;
        $this->content = \Yii::$app->controller->renderPartial(static::TEMPLATE_PATH_ALIAS, [
            'formModel' => $form,
            'signaturePath' => $this->signaturePath,
            'ip' => $this->ip
        ]);
        $this->fileName = $form->clientId . ".pdf";
    }
}
