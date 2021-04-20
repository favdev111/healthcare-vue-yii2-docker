<?php

namespace common\models;

use common\components\behaviors\TimestampBehavior;
use modules\task\queueJobs\SendLead;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * This is the model class for table "{{%leads}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $phoneNumber
 * @property string $email
 * @property array $data
 * @property string $advertisingChannel
 * @property string $clickId
 * @property string $source
 * @property integer $status
 * @property string $externalId
 * @property string $ip
 * @property string $updatedAt
 * @property string $createdAt
 *
 * @property-read string $salesForceLinkAttribute
 * @property-read string $description
 */
class Lead extends \yii\db\ActiveRecord
{
    public const ADVERTISING_CHANNEL_GOOGLE_ADS = 'Google Ads / PPC';
    public const ADVERTISING_CHANNEL_BING_ADS = 'Bing';
    public const ADVERTISING_CHANNEL_ORGANIC = 'Organic';

    public const QUEUE_STATUS_PENDING = 0;
    public const QUEUE_STATUS_OK = 1;
    public const QUEUE_STATUS_ERROR = 2;

    public const QUEUE_STATUS_LABELS = [
        self::QUEUE_STATUS_PENDING => 'pending',
        self::QUEUE_STATUS_OK => 'ok',
        self::QUEUE_STATUS_ERROR => 'error',
    ];

    public const SOURCE_SIGNUP_FREE_HEALTH_CONSULTATION = 'Sign Up for a Free Health Consultation';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'status' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterSave' => true,
                'typecastAfterFind' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%leads}}';
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->status = self::QUEUE_STATUS_PENDING;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->addToQueue();
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    public function addToQueue(bool $force = false)
    {
        return Yii::$app
            ->yiiQueue
            ->priority(1)
            ->push(new SendLead([
                'leadModelId' => $this->id,
                'force' => $force,
            ]));
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @return null|string
     */
    public function getSalesForceLinkAttribute(): ?string
    {
        return $this->externalId ? Yii::$app->salesforce->createUrlToLead($this->externalId) : null;
    }
}
