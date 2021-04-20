<?php
/** @var \yii\web\View $this */
use yii\helpers\Html;
use yii\helpers\StringHelper;

/** @var \modules\account\models\Account $model */

\frontend\assets\SwiperAsset::register($this);
$this->registerJs(<<<JS
var swiper = new Swiper('.swiper-container', {
    slidesPerView: 3,
    spaceBetween: 30,
    loop: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    // Responsive breakpoints
    breakpoints: {
        // when window width is <= 600px
        600: {
            slidesPerView: 1,
        },
        // when window width is <= 991px
        991: {
            slidesPerView: 2,
        }
    }
});
JS
);

$countReview = $model->getCountReview();
?>
<div class="top-tutors-card text-center">
    <span class="top-tutors__card__object">
        <img class="img-circle" src="<?= $model->avatarUrl; ?>" alt="TUTOR'S AVATAR">
    </span>
    <div class="caption top-tutors-card__caption">
        <div class="top-tutors-card__caption_rating">
            <div class="star-block star-block--star-yellow star-block--tutor-info">
                <div class="flex-media__rate flex-media__rate--center">
                    <div class="rating-container rating-animate rating-disabled">
                        <div class="rating">
                             <span class="empty-stars">
                                 <span class="star">
                                     <i class="fa fa-star star-grey"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-grey"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-grey"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-grey"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-grey"></i></span>
                             </span>
                             <span class="filled-stars" style="width: <?= $model->totalRating/0.05 ?>%;">
                                 <span class="star">
                                     <i class="fa fa-star star-yellow"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-yellow"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-yellow"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-yellow"></i></span>
                                 <span class="star">
                                     <i class="fa fa-star star-yellow"></i></span>
                             </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p class="top-tutors-card__total_reviews">
            <span class="fw-semi"><?= $countReview ?> reviews, </span><span  class="fw-semi"> <?= $model->getTotalTeachHours() ?> hours tutored</span>
        </p>
        <p class="top-tutors-card__caption_name">
            <a class="text-underline" href="<?= \common\helpers\Url::to(['/account/profile-tutor/tutor-info', 'id' => $model->id]) ?>"><?= $model->profile->showName ?></a>
            <?php if ($model->profile->cityName): ?>
            from <?= $model->profile->cityName ?>
            <?php endif; ?>
        </p>
        <p class="top-tutors-card__caption_subject">
            <?php if ($subject = $model->getAccountSubjects()->one()) : ?>
                <strong><?= $subject->name ?> </strong>-
            <?php endif; ?>
            Tutor
        <?php if ($model->profile->description) : ?>
            <div class="top-tutors-card__caption_text top-tutors-card__caption_text--white thumbnail">
                <div class="top-tutors-card__text-wrapper top-tutors-card__text-wrapper--white">
                    <p><?= \yii\helpers\HtmlPurifier::process(StringHelper::truncate($model->profile->getDescription(), 200, '... ', null, true)); ?></p>
                </div>
            </div>
        <?php endif; ?>
        </p>
    </div>
</div>
