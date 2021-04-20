<?php

namespace common\models;

use modules\account\models\Account;

class SpecialistAgreementPdf extends Pdf
{
    protected $specialistAccount;
    const TEMPLATE_PATH_ALIAS = '@themes/basic/common/views/parts/specialistAgreement';
    const FILE_NAME = 'TUTOR INDEPENDENT CONTRACTING Agreement';
    public $specialistId;
    public $date;
    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                'date',
                'date',
                'format' => 'php:m/d/Y',
            ],
            [['date'], 'string'],
            [['specialistId'], 'integer'],
            [['specialistId'], 'exist', 'targetClass' => Account::class, 'targetAttribute' => 'id'],
        ]);
    }

    public function getSpecialist(): Account
    {
        if (empty($this->specialistAccount)) {
            /**
             * @var Account $account
             */
            $account = Account::find()->with('profile')->andWhere(['id' => $this->specialistId])->limit(1)->one();
            $this->specialistAccount = $account;
        }
        return $this->specialistAccount;
    }

    public function prepareContent()
    {
        $this->content = \Yii::$app->controller->renderPartial(static::TEMPLATE_PATH_ALIAS, [
            'specialistName' => $this->getSpecialist()->profile->fullName(),
            'date' => $this->date
        ]);
        $this->fileName = $this->specialistId . ".pdf";
    }
}
