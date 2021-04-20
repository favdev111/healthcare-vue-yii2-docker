<?php

namespace common\components\behaviors;

use common\components\ActiveRecord;
use Yii;
use yii\imagine\Image;

/**
 * Base64 Image behavior
 * @inheritdoc
 */
class Base64ImageBehavior extends \yii\base\Behavior
{
    /**
     * @var ActiveRecord the owner of this behavior
     * Overriding field to adjust PhpDoc
     */
    public $owner;

    /**
     * @var string[] Local variable to store decoded images
     */
    protected $base64Image;

    /**
     * @var array Image EXIF orientation
     */
    protected $orientation = [];

    /**
     * @var string[] The attributes to get and set image
     */
    public $attribute = 'imageBase64';

    /**
     * @var array List of allowed extensions
     */
    public $allowedMimeTypes = [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
    ];

    protected $minWidth = 80;
    protected $minHeight = 80;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidateHandler',
        ];
    }

    public function beforeValidateHandler()
    {
        $attribute = $this->attribute;
        $newValue = $this->owner->$attribute;

        if (is_null($newValue)) {
            return;
        }

        $data = explode(',', $newValue);
        $this->base64Image = isset($data[1]) ? base64_decode($data[1]) : false;

        try {
            $exif = exif_read_data($newValue);
            $this->orientation = $exif['Orientation'] ?? null;
        } catch (\Exception $e) {
            // In case of any error we do not need to transform anything based on orientation
            $this->orientation = null;
        }

        try {
            $imageSize = getimagesizefromstring($this->base64Image, $imageInfo);
            if (!empty($imageSize[0]) && !empty($imageSize[1])) {
                if ($imageSize[0] < $this->minWidth || $imageSize[1] < $this->minHeight) {
                    $this->owner->addError($attribute, $this->owner->getAttributeLabel($attribute) . ' size should be at least ' . $this->minWidth . ' x ' . $this->minHeight . '.');
                }
            }
            if (!in_array($imageSize['mime'], $this->allowedMimeTypes)) {
                $this->owner->addError($attribute, $this->owner->getAttributeLabel($attribute) . ' is invalid image type.');
            }
            if ($this->base64Image === false) {
                $this->owner->addError($attribute, $this->owner->getAttributeLabel($attribute) . ' is invalid.');
            }
        } catch (\Exception $e) {
            Yii::error('Failed to parse image. Error: ' . $e->getMessage(), 'image');
            $this->owner->addError($attribute, $this->owner->getAttributeLabel($attribute) . ' is invalid.');
        }
    }

    protected function rotateAndFlip($image, $orientation)
    {
        switch ($orientation) {
            case 2:
                $image->flipVertically();
                break;
            case 3:
                $image->rotate(180);
                break;
            case 4:
                $image->flipHorizontally();
                $image->rotate(180);
                break;
            case 5:
                $image->flipHorizontally();
                $image->rotate(-90);
                break;
            case 6:
                $image->rotate(90);
                break;
            case 7:
                $image->flipVertically();
                $image->rotate(-90);
                break;
            case 8:
                $image->rotate(-90);
                break;
        }
    }

    public function saveImage($savePath)
    {
        if (empty($this->base64Image)) {
            return;
        }

        $image = Image::getImagine()->load($this->base64Image);
        $this->rotateAndFlip($image, $this->orientation);
        $image->save($savePath, ['quality' => 100]);
    }
}
