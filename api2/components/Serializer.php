<?php

namespace api2\components;

use common\components\Response;
use Yii;

/**
 * Serializer converts resource objects and collections into array representation.
 *
 * Serializer is mainly used by REST controllers to convert different objects into array representation
 * so that they can be further turned into different formats, such as JSON, XML, by response formatters.
 *
 * The default implementation handles resources as [[Model]] objects and collections as objects
 * implementing [[DataProviderInterface]]. You may override [[serialize()]] to handle more types.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Serializer extends \yii\rest\Serializer
{
    public $responseClass;

    /**
     * @inheritDoc
     */
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {
            return null;
        }

        $returnModel = null;
        if ($this->responseClass && !($model instanceof Response)) {
            $class = $this->responseClass;
            $returnModel = new $class($model);
        } else {
            $returnModel = $model;
        }

        return parent::serializeModel($returnModel);
    }

    /**
     * @inheritDoc
     */
    protected function serializeModels(array $models)
    {
        $returnModels = [];
        if ($this->responseClass) {
            $class = $this->responseClass;
            foreach ($models as $i => $model) {
                $returnModels[$i] = ($model instanceof Response) ? $model : new $class($model);
            }
        } else {
            $returnModels = $models;
        }

        return $returnModels;
    }
}
