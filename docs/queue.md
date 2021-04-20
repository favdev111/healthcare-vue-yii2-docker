## Yii2 Queue

### Add job to queue

**Create job class**

Use `\modules\task\components\RetryableJob` or create skeleton to extend in job class

```php
class DownloadJob extends \modules\task\components\RetryableJob
{
    public $url;
    public $file;
    
    public function execute($queue)
    {
        file_put_contents($this->file, file_get_contents($this->url));
    }
}
```

**Push job task to queue**

```php
\Yii::$app->yiiQueue->push(new DownloadJob([
    'url' => 'http://example.com/image.jpg',
    'file' => '/tmp/image.jpg',
]));
```

### Send email via queue

```php
\Yii::$app->yiiQueue->push(new MailerJob([
    'view' => $view,
    'params' => $params,
    'email' => $email,
    'subject' => $subject,
    'viewPath' => $viewPath,
    'useHtmlLayout' => $useHtmlLayout
]));
```

### Queue cli commands

```
yii-queue/clear            Clears the queue.
yii-queue/exec             Executes a job.
yii-queue/info (default)   Info about queue status.
yii-queue/listen           Listens db-queue and runs new jobs.
yii-queue/remove           Removes a job by id.
yii-queue/run              Runs all jobs from db-queue.
```
