<?php

use themes\basic\backend\widgets\Menu;

?>

<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="<?php echo \common\helpers\Url::home() ?>">
              <img src="<?= $this->theme->getUrl('img/logo.png') ?>" alt="logo" height="30" />
            </a>
        </div>
        <?= Menu::widget(['options' => ['class' => 'sidebar-menu']]) ?>
    </aside>
</div>
