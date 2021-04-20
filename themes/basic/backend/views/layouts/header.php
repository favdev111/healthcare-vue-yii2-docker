<?php
use yii\helpers\Html;

use backend\components\widgets\HeaderNotification\Widget as HeaderNotificationWidget;


/* @var $this \yii\web\View */
/* @var $content string */

echo $this->render('@themes/basic/backend/views/layouts/_jsApp');

?>
<!-- navbar background color -->
<div class="navbar-bg"></div>
<!-- navbar -->
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
      <ul class="navbar-nav mr-3">
        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
        <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
      </ul>
    </form>
    <!-- navbar right -->
    <ul class="navbar-nav navbar-left">
        <!-- navbar notification toggle -->
        <?=

        HeaderNotificationWidget::widget([])
        ?>

        <!-- navbar right item -->
        <li class="dropdown">
          <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            Hi, <?= Yii::$app->user->identity->displayName ?>
          </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="<?php echo \common\helpers\Url::to(['/account/admin/update', 'id' => Yii::$app->user->id]) ?>" class="dropdown-item has-icon">
                  <i class="far fa-user"></i> Profile
                </a>
                <?= Html::beginForm(['/site/logout']) ?>
                <?= Html::submitButton('Sign out', ['class' => 'dropdown-item has-icon text-danger']) ?>
                <?= Html::endForm() ?>
            </div>
        </li>
    </ul>
</nav>

