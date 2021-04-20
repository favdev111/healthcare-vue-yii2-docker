<?php
$this->title = 'Update Label Category';
$this->params['breadcrumbs'][] = ['label' => 'Label Category', 'url' => ['/labels/categories']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-update">
    <?= $this->render('_form', ['model' => $model,]) ?>
</div>
