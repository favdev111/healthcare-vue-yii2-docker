<?php

/** @var \yii\web\View $this */
/** @var \yii\data\ActiveDataProvider $dataProvider */

?>
<?php if ($dataProvider->getCount()) : ?>
<!-- Swiper -->
<div class="swiper-container top-tutors-carousel">
    <?= \yii\widgets\ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_tutor_slide',
        'itemOptions' => [
            'class' => 'swiper-slide',
        ],
        'options' =>[
            'class' => 'list-view swiper-wrapper'
        ],
        'layout' => "{items}",
    ]); ?>
    <!-- Add Arrows -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>
<?php else: ?>
    <div class="top-tutors-carousel-empty"></div>
<?php endif; ?>
