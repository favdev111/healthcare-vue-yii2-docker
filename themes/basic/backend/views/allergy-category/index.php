<?php

use common\models\health\allergy\AllergyCategory;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\allergy\AllergyCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Allergy Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="allergy-category-index">
    <p>
        <?= Html::a('Create Allergy Category', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'name',
                    [
                            'label' => 'Medical allergy group',
                            'format' => 'boolean',
                            'value' => static function (AllergyCategory $allergyCategory) {
                                return $allergyCategory->getMedicalAllergyGroup()->exists();
                            },
                    ],
                    ['class' => 'backend\components\rbac\column\ActionColumn']
            ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
