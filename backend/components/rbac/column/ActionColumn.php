<?php

namespace backend\components\rbac\column;

use yii\grid\ActionColumn as BaseActionColumn;
use Yii;
use yii\helpers\Html;

/**
 * Class ActionColumn
 * @package backend\components\rbac\column
 */
class ActionColumn extends BaseActionColumn
{
    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string|string[]|null
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $content = parent::renderDataCellContent($model, $key, $index);
        return Html::tag('div', $content, ['class' => 'btn-group']);
    }

    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye-open fas fa-eye', ['class' => 'btn btn-primary btn-sm']);
        $this->initDefaultButton('update', 'pencil fas fa-edit', ['class' => 'btn btn-info btn-sm']);
        $this->initDefaultButton('delete', 'trash fas fa-trash', [
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
            'class' => 'btn btn-danger btn-sm'
        ]);
    }
}
