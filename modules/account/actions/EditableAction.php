<?php

namespace modules\account\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\web\Response;

class EditableAction extends Action
{
    public $model;

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($this->model)) {
            throw new InvalidArgumentException('Model mas be set.');
        }

        if (!Yii::$app->request->post('hasEditable')) {
            return;
        }

        $id = Yii::$app->request->post('editableKey');
        $modelClass = $this->model;
        $model = $modelClass::findOne($id);

        $out = ['output' => '', 'message' => ''];

        $formName = $model->formName();
        $posted = current($_POST[$formName]);
        $post = [$formName => $posted];

        // load model like any single model validation
        if ($model->load($post)) {
            $model->save();
            $output = '';
            if (isset($posted['status'])) {
                $output = $model->statusName;
            }

            if ($model->hasErrors()) {
                $out['message'] = current($model->getFirstErrors());
            }

            $out['output'] = $output;
        }

        return $out;
    }
}
