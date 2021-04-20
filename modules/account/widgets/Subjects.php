<?php

namespace modules\account\widgets;

use modules\account\models\Category;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Class Subjects
 * @package modules\account\widgets
 * @todo Need refactoring for mobile edit subjects in settings
 */
class Subjects extends Widget
{
    public $account = null;
    public $template = 'view';

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $catSubjRel = [];
        $categories = Category::find()->indexBy('id')->orderBy('sort')->all();
        foreach ($categories as $key => $category) {
            $catSubjRel[$key]['title'] = $category->name;
            $catSubjRel[$key]['subjects'] = $category->subjects;
        }
        $subjects = [];
        if (!Yii::$app->user->isGuest) {
            if (!$this->account) {
                $this->account = Yii::$app->user->identity;
            }
            $subjects = $this->account->getSubjects()->select(['subjectId'])->asArray()->column();
        }

        return $this->render($this->template, [
            'catSubjRel' => $catSubjRel,
            'subjects' => $subjects
        ]);
    }
}
