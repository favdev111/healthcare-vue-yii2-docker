<?php

namespace modules\account\models;

use common\components\ActiveRecord;
use common\components\behaviors\TimestampBehavior;
use modules\account\helpers\JobHelper;
use Yii;
use yii\db\ActiveQuery;
use yii\web\UploadedFile;

/**
 * Class FileModel
 * @package modules\account\models\files
 *
 * @property int $id
 * @property string $file_name
 * @property int $status
 * @property string $mime_type
 * @property int $size
 * @property int $createdBy
 * @property string|null $originalFileName
 * @property int|null $job_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Job $job
 * @property-read Account $creator
 */
class FileModel extends ActiveRecord
{
    /**
     *
     */
    public const STATUS_ATTACHED = 1;
    /**
     *
     */
    public const STATUS_DETACHED = 0;
    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%files}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $maxFileSize = Yii::$app->formatter->asShortSize(JobHelper::getMaxFileSize());
        return [
            [
                ['file'],
                'file',
                'extensions' => JobHelper::getFileExtensions(),
                'mimeTypes' => JobHelper::getFileMimeTypes(),
                'skipOnEmpty' => false,
                'maxSize' => JobHelper::getMaxFileSize(),
                'checkExtensionByMimeType' => false,
                'tooBig' => "The file you are trying to upload is too large. Max allowed size is ${maxFileSize}"
            ],
            [
                'file',
                function ($attribute, $params) {
                    if (
                        isset($this->job->attachedFiles) &&
                        count($this->job->attachedFiles) >= JobHelper::getMaxFiles()
                    ) {
                        $this->addError($attribute, 'Max files limit exceeded');
                    }
                },
            ],
        ];
    }

    /**
     * @return array|false
     */
    public function fields()
    {
        return [
            'id',
            'name' => function () {
                return $this->file_name;
            },
            'size',
            'mimeType' => function () {
                return $this->mime_type;
            },
            'status',
            'originalFileName' => function () {
                return $this->originalFileName ? $this->originalFileName : $this->file_name;
            },
            'downloadUrl' => function () {
                return \Yii::$app->urlManager->createAbsoluteUrl(['/account/files/view', 'id' => $this->id]);
            }
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCreator(): ActiveQuery
    {
        return $this->hasOne(Account::class, ['id' => 'createdBy']);
    }

    /**
     * @return ActiveQuery
     */
    public function getJob(): ActiveQuery
    {
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }
}
