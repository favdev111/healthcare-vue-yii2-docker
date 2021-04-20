<?php

/* @var $this yii\web\View */

/* @var $model Account */

use api2\helpers\DoctorType;
use api2\helpers\EnrolledTypes;
use api2\helpers\ProfessionalType;
use modules\account\models\Account;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'table table-bordered table-hover'],
    'attributes' => [
        [
            'label' => 'Type of health care professional',
            'value' => static function (Account $account) {
              return ProfessionalType::getType($account->profile->professionalTypeId);
            }
        ],
        'profile.yearsOfExperience:text:Years of experience',
        [
            'label' => 'Doctor type',
            'visible' => $model->profile->professionalTypeId === ProfessionalType::DOCTOR,
            'value' => static function (Account $account) {
              return DoctorType::getDoctorType($account->profile->doctorTypeId);
            }
        ],
        [
            'label' => 'NPI #',
            'visible' => $model->profile->professionalTypeId === ProfessionalType::DOCTOR,
            'attribute' => 'profile.npiNumber'
        ],
        'profile.isBoardCertified:boolean:Board certified',
        [
            'label' => 'Accept Medicare/Medicaid',
            'value' => static function (Account $account) {
              return EnrolledTypes::getType($account->profile->currentlyEnrolled);
            }
        ],
        [
            'format' => 'html',
            'contentOptions' => ['class' => 'p-0'],
            'label' => 'Licenses',
            'value' => static function (Account $account) {
              $provider = Yii::createObject([
                  'class' => ActiveDataProvider::class,
                  'query' => $account->getLicenceStates(),
                  'sort' => false,
                  'pagination' => false
              ]);

              return GridView::widget([
                  'dataProvider' => $provider,
                  'tableOptions' => ['class' => 'table m-0'],
                  'emptyText' => Yii::$app->formatter->nullDisplay,
                  'pager' => false,
                  'summary' => false,
                  'columns' => [
                      'state.name:text:State',
                      'licence',
                  ],
              ]);
            }
        ],
        [
            'format' => 'html',
            'contentOptions' => ['class' => 'p-0'],
            'label' => 'State(s) of legally allowed to practice telehealth',
            'value' => static function (Account $account) {
              $provider = Yii::createObject([
                  'class' => ActiveDataProvider::class,
                  'query' => $account->getTelehealthStates(),
                  'sort' => false,
                  'pagination' => false
              ]);

              return GridView::widget([
                  'dataProvider' => $provider,
                  'tableOptions' => ['class' => 'table m-0'],
                  'emptyText' => Yii::$app->formatter->nullDisplay,
                  'pager' => false,
                  'summary' => false,
                  'showHeader' => false,
                  'columns' => [
                      'state.name:text:State',
                  ],
              ]);
            }
        ],
        [
            'format' => 'html',
            'contentOptions' => ['class' => 'p-0'],
            'label' => 'Accept insurances',
            'value' => static function (Account $account) {
              $provider = Yii::createObject([
                  'class' => ActiveDataProvider::class,
                  'query' => $account->getAccountInsurance(),
                  'sort' => false,
                  'pagination' => false
              ]);

              return GridView::widget([
                  'dataProvider' => $provider,
                  'tableOptions' => ['class' => 'table m-0'],
                  'emptyText' => Yii::$app->formatter->nullDisplay,
                  'pager' => false,
                  'summary' => false,
                  'showHeader' => false,
                  'columns' => [
                      'insuranceCompany.name',
                  ]
              ]);
            }
        ],
        [
            'format' => 'html',
            'contentOptions' => ['class' => 'p-0'],
            'label' => 'Disciplinary actions',
            'value' => static function (Account $account) {
              return DetailView::widget([
                  'model' => $account->profile,
                  'options' => ['class' => 'table table-bordered m-0'],
                  'attributes' => [
                      'hasDisciplinaryAction:boolean',
                      'disciplinaryActionText',
                  ]
              ]);
            }
        ]
    ]
]) ?>

<h2>Specifications</h2>
<?= $this->render('_specifications', ['model' => $model]) ?>
