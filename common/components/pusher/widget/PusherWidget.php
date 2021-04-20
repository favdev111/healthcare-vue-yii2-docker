<?php

namespace common\components\pusher\widget;

use common\components\pusher\Pusher;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/**
 * Class PusherWidget
 * @package common\components\pusher\widget
 */
class PusherWidget extends Widget
{
    /**
     * @var array
     */
    public $events = [];
    /**
     * @var
     */
    public $pusher;

    /**
     * @var array
     */
    private $defaultOptions = [
        'cluster' => 'eu',
        'forceTLS' => true,
    ];

    /**
     * @var array
     */
    public $options = [];

    /**
     *
     */
    public function init()
    {
        $this->pusher = Yii::$app->pusher;
        parent::init();
    }

    /**
     * @return string|void
     */
    public function run()
    {
        if (!$this->pusher) {
            return;
        }
        Yii::$app->view->registerJsFile('https://js.pusher.com/5.0/pusher.min.js');
        $channel = Yii::$app->user->identity->publicId;
        $events = Json::encode($this->events);

        if (isset(Yii::$app->pusher->options['cluster'])) {
            $this->defaultOptions['cluster'] = Yii::$app->pusher->options['cluster'];
        }
        $options = Json::encode(array_unique(array_merge($this->defaultOptions, $this->options)));
        \Yii::$app->view->registerJs(
            'var pusher = new Pusher("' . $this->pusher->appKey . '",' . $options . ');
            var channel = pusher.subscribe("private-' . $channel . '");
            var events = ' . $events . ';
            for (var name in events) {
                channel.bind(name, events[name]);
            }',
            View::POS_READY
        );
        return Html::tag('div', $channel, ['id' => 'pusher-channel', 'class' => 'hide']);
    }
}
