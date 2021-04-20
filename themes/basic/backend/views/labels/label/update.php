<?php
$this->title = 'Update Label';
$this->params['breadcrumbs'][] = ['label' => 'Labels', 'url' => ['/labels/label']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-update">
    <?= $this->render('_form', ['model' => $model,]) ?>
</div>
