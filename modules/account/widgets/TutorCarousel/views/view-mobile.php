<?php

/** @var \yii\web\View $this */
/** @var \yii\data\ActiveDataProvider $dataProvider */

?>
<?php if ($dataProvider->getCount()) : ?>
        <?= \yii\widgets\ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => function ($model, $key, $index, $widget) use ($dataProvider) {
                return $this->render(
                    '@modules/account/widgets/TutorCarousel/views/_tutor_slide_mobile',
                    [
                        'model' => $model,
                        'index' => $index,
                        'count' => $dataProvider->getCount()
                    ]
                );
            },
            'itemOptions' => [
                'class' => '',
            ],
            'options' => [
                'class' => ''
            ],
            'layout' => "{items}",
        ]); ?>
<?php else: ?>
    <div class="top-tutors-carousel-empty"></div>
<?php endif; ?>