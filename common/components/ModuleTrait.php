<?php

namespace common\components;

use Yii;
use yii\i18n\PhpMessageSource;

/*
 * Trait for modules in the system
 */
trait ModuleTrait
{
    /**
     * @var array Model classes, e.g., ["Model" => "path\to\model"]
     * Usage:
     *   $user = Yii::$app->getModule("name")->model("Model", $config);
     *   (equivalent to)
     *   $model = new \path\to\model\Model($config);
     *
     * The model classes here will be merged with/override the [[getDefaultModelClasses()|default ones]]
     */
    public $modelClasses = [];

    /**
     * @var string Email view path
     */
    public $emailViewPath;

    public function setPaths($check = true)
    {
        $id = Yii::$app->id;
        if (!$check || ($check && $this->controllerNamespace === null)) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $this->controllerNamespace = substr(
                    $class,
                    0,
                    $pos
                ) . '\\controllers\\' . $id;
            }
        }

        //Set path to views for module application
        $this->setViewPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $id);

        $this->emailViewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'mail';

        //Add translations folder
//        Yii::$app->get('i18n')->translations[$this->id . '*'] = [
//            'class'    => PhpMessageSource::className(),
//            'basePath' => $this->basePath . DIRECTORY_SEPARATOR . 'messages',
//        ];
    }

    public function init()
    {
        $this->setPaths();

        // override modelClasses
        $this->modelClasses = array_merge(
            $this->getDefaultModelClasses(),
            $this->modelClasses
        );

        parent::init();
    }

    /**
     * Get object instance of model
     *
     * @param string $name
     * @param array $config
     *
     * @return ActiveRecord
     * @throws \yii\base\InvalidConfigException
     */
    public function model($name, $config = [])
    {
        $config['class'] = $this->modelClasses[ucfirst($name)];
        $object = Yii::createObject($config);
        if ($object->hasProperty('module') && empty($object->module)) {
            $object->module = $this;
        }

        return $object;
    }

    public function modelStatic($name)
    {
        return $this->modelClasses[ucfirst($name)];
    }

    /**
     * Get default model classes
     */
    protected function getDefaultModelClasses()
    {
        return [

        ];
    }

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        $appId = Yii::$app->id;
        if (method_exists($this, $appId)) {
            $this->{$appId}($app);
        }
    }

    protected function backend($app)
    {
    }
}
