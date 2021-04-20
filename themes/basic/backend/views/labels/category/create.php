<?php
$this->title = 'Create Label Category';
$this->params['breadcrumbs'][] = ['label' => 'Label Category', 'url' => ['/labels/categories']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-create">
    <?= $this->render('_form', ['model' => $model,]) ?>
</div>
