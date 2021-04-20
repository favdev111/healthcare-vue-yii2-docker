<?php

namespace backend\components\widgets\content;

/**
 * Class DetailView
 * @package backend\components\widgets\content
 */
class DetailViewList extends \yii\widgets\DetailView
{
    public $options = [
        'tag' => 'ul',
        'class' => 'list-group list-group-unbordered',
    ];

    public $template = '<li {captionOptions} class="list-group-item"><b>{label}</b> <a {contentOptions} class="float-right">{value}</a></li>';
}
