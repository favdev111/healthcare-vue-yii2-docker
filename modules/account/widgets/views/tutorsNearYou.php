<div class="tutors-near">
<?php
foreach ($data as $d):
    if (empty($d['review'])) {
        continue;
    }
?>
    <a class="tutor-near-item" href="">
        <img src="<?= $d['review']->tutor->avatarUrl ?>" alt="<?= $d['model']->name ?> tutor">
        <div class="rating-container">
            <div class="rating rating--has-small-stars">
                <?= \kartik\widgets\StarRating::widget([
                    'name' => 'rating',
                    'value' => $d['review']->tutor->totalRating,
                    'pluginOptions' => [
                        'step' => 0.1,
                        'size' => '',
                        'readonly' => true,
                        'filledStar' => '<i class="fa fa-star star-yellow"></i>',
                        'emptyStar' => '<i class="fa fa-star star-grey"></i>',
                        'showClear' => false,
                        'showCaption' => false,
                    ]
                ]); ?>
            </div>
        </div>
        <h3><?= $d['model']->name ?> Tutors</h3>
    </a>
<?php
    endforeach;
?>
</div>
