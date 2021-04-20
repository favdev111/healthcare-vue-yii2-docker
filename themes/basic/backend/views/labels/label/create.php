<?php
$this->title = 'Create Label';
$this->params['breadcrumbs'][] = ['label' => 'Labels', 'url' => ['/labels/label']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="label-create">
    <?= $this->render('_form', ['model' => $model,]) ?>
</div>
