<?php

namespace common\components;

use League\Flysystem\AdapterInterface;
use Yii;
use yii\imagine\Image;

trait UploadTrait
{
    public function getThumbnailTypes()
    {
        return [
            UploadInterface::THUMBNAIL_TYPE_LARGE => ['width' => 600, 'height' => 600],
            UploadInterface::THUMBNAIL_TYPE_MEDIUM => ['width' => 300, 'height' => 300],
            UploadInterface::THUMBNAIL_TYPE_SMALL => ['width' => 150, 'height' => 150],
        ];
    }

    public function getImageName()
    {
        return $this->getPrimaryKey();
    }

    public function getImagePath()
    {
        return 'avatar';
    }

    /**
     * @param \yii\web\UploadedFile $file
     * @param bool $saveOrigin
     */
    public function createThumbnails(\yii\web\UploadedFile $file, bool $saveOrigin = true)
    {
        $awsS3FileSystem = Yii::$app->awsS3FileSystem;
        $name = $this->getImageName();
        $path = trim($this->getImagePath(), '/') . "/${name}";

        $awsS3FileSystem->deleteDir($path);

        if ($saveOrigin) {
            $awsS3FileSystem->write(
                $path . '/' . UploadInterface::THUMBNAIL_TYPE_ORIGIN,
                file_get_contents($file->tempName),
                [
                    'visibility' => AdapterInterface::VISIBILITY_PRIVATE,
                    'ACL' => 'private',
                ]
            );
        }

        foreach ($this->getThumbnailTypes() as $typeName => $options) {
            if (empty($options['width']) || empty($options['height'])) {
                continue;
            }

            $image = Image::thumbnail(
                $file->tempName,
                $options['width'] ?? null,
                $options['height'] ?? null
            );

            $awsS3FileSystem->write(
                "${path}/${typeName}.jpg",
                $image->get('jpg', ['jpeg_quality' => 90])
            );
        }

        $this->hasPhoto = true;
        $this->save(false);
    }

    public function getLargeThumbnailUrl(): ?string
    {
        return $this->getThumbnailUrl(UploadInterface::THUMBNAIL_TYPE_LARGE);
    }

    public function getMediumThumbnailUrl(): ?string
    {
        return $this->getThumbnailUrl(UploadInterface::THUMBNAIL_TYPE_MEDIUM);
    }

    public function getSmallThumbnailUrl(): ?string
    {
        return $this->getThumbnailUrl(UploadInterface::THUMBNAIL_TYPE_SMALL);
    }

    public function getThumbnailUrl(string $type): ?string
    {
        if (!$this->hasPhoto) {
            return null;
        }

        $prefix = trim(Yii::$app->awsS3FileSystem->prefix, '/');
        $name = $this->getImageName();
        $path = ($prefix ? "${prefix}/" : '') . trim($this->getImagePath(), '/') . "/${name}";
        return $name && $path && $type && Yii::$app->awsS3FileSystem->getAdapter()->getClient() ?
            Yii::$app->awsS3FileSystem->getAdapter()->getClient()->getObjectUrl(
                Yii::$app->awsS3FileSystem->bucket,
                "${path}/${type}.jpg"
            ) . '?t=' . crc32($this->updatedAt ?? time()) : '';
    }
}
