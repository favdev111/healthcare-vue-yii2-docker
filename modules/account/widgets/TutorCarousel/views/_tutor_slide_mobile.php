<?php
/** @var \yii\web\View $this */
use yii\helpers\Html;
use common\components\StringHelper as CommonStringHelper;

/** @var \modules\account\models\Account $model */

?>

<div class="top-tutors-card">
    <div class="top-tutors-card__left text-center">
        <img class="img-circle" src="<?= $model->avatarUrl; ?>" alt="TUTOR'S AVATAR">
        <a href="<?= \common\helpers\Url::to(['/account/profile-tutor/tutor-info', 'id' => $model->id]) ?>" class="text-underline fw-bold"><?= $model->profile->showName ?></a>
        <div class="rating-container rating-container--top-tutors-card">
            <div class="rating">
                    <span class="empty-stars">
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                    </span>
                    <span class="filled-stars" style="width: <?= $model->totalRating/0.05 ?>%;">
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                        <span class="star"><i class="fa fa-star"></i></span>
                    </span>
            </div>
        </div>
    </div>
    <div class="top-tutors-card__body">
        <div class="top-tutors-card__text-wrapper">
            <p class="mb0"><?= \yii\helpers\HtmlPurifier::process(CommonStringHelper::truncate($model->profile->getDescription(), 160, '...', null, true)); ?></p>
        </div>
    </div>
</div>
<?php if ($index < $count - 1) : ?>
<span class="divider"></span>
<?php endif; ?>

