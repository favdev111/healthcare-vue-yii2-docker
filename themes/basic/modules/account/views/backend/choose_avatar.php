<?php
use modules\account\models\backend\Account;
use yii\helpers\Html;
?>
<div class="choose-avatar">
    <div class="dropdown">
        <a href="##" class="btn--choose-avatar" data-toggle="dropdown">Choose your Avatar <span class="caret"></span></a>
        <div class="dropdown-menu dropdown-menu--avatar">
            <?php
            foreach ($model->avatars as $avatar) : ?>
                <a class="dropdown-menu__item dropdown-menu__item--avatar" href="##" data-img="<?= $avatar ?>">
                    <img src="<?= $frontendAsset->baseUrl . ('/img/avatar/'.$avatar) ?>" />
                </a>
            <?php endforeach; ?>
            <input type="hidden" name="<?= Html::getInputName($model, 'avatar') ?>" id="student-avatar" value="<?= $profile->studentAvatarId ? Account::$avatars[$profile->studentAvatarId] : '' ?>">
        </div>
    </div>
    <span class="choose-avatar__object">
            <img class="img-responsive img-circle" src="<?= $profile->studentAvatarId ? $this->theme->getUrl('img/avatar/'.Account::$avatars[$profile->studentAvatarId]) : $this->theme->getUrl('img/avatar-placeholder-sm.png') ?>" />
        </span>
</div>
<?php
$this->registerJs(
    <<<'JS'
    jQuery(function () {
        $('.dropdown-menu__item--avatar').on('click', function (e) {
            var img = $(this).data('img');
            $('#student-avatar').val(img);
            var path = $(this).find('img').attr('src');
            $('.choose-avatar__object img').attr('src', path);
        });
    });
JS
);
?>
