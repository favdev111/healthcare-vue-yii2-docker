<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use common\components\HtmlPurifier;
use common\components\validators\MailRuValidator;
use common\components\validators\NameStringValidator;
use modules\account\models\search\JobSearch;
use Yii;
use yii\db\IntegrityException;

/**
 * This is the model class for table "{{%job_lead}}".
 *
 * @property integer $id
 * @property string $firstName
 * @property string $lastName
 * @property string $phoneNumber
 * @property string $email
 * @property integer $jobId
 * @property integer $accountId
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $account
 * @property Job $job
 */
class JobLead extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job_lead}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firstName', 'lastName', 'phoneNumber'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['firstName', 'lastName', 'email', 'phoneNumber' ,'jobId'], 'required'],
            [['firstName', 'lastName'], NameStringValidator::class],
            [['firstName'], 'filter', 'filter' => function ($value) {
                return ucwords(strtolower($value));
            }
            ],
            [['lastName'], 'filter', 'filter' => function ($value) {
                return ucwords(strtolower($value));
            }
            ],
            [['email'], 'email', 'checkDNS' => true],
            [['email'], MailRuValidator::class],
            [['jobId'], 'integer'],
            [['firstName', 'lastName', 'email'], 'string', 'max' => 255],
            [['phoneNumber'], 'string', 'max' => 10],
            ['phoneNumber', 'udokmeci\yii2PhoneValidator\PhoneValidator','country' => 'US', 'format' => false],
            [['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::class, 'targetAttribute' => ['jobId' => 'id']],
            [['jobId'], 'validateJob', 'skipOnError' => true],
        ];
    }

    public function validateJob($attribute, $params)
    {
        $searchModel = new JobSearch();
        $dataProvider = $searchModel->tutorJobSearch([], -1);
        $query = clone $dataProvider->query;

        if (!$query->andWhere([Job::tableName() . '.id' => $this->$attribute])->exists()) {
            $this->addError($attribute, 'Job not found.');
        }
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    public function fields()
    {
        return [
            'firstName',
            'lastName',
            'phoneNumber',
            'email',
        ];
    }

    /**
     * Do not save duplicate data
     *
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (
            $this->isNewRecord
            && static::find()->andWhere([
                'jobId' => $this->jobId,
                'email' => $this->email,
                'phoneNumber' => $this->phoneNumber,
            ])->exists()
        ) {
            return true;
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'phoneNumber' => 'Phone Number',
            'email' => 'Email',
            'jobId' => 'Job ID',
            'accountId' => 'Account ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'jobId']);
    }

    public static function setAccountIdByParams($accountId, $email, $phoneNumber)
    {
        $model = self::find()
            ->andWhere(['accountId' => null])
            ->andWhere([
                'or',
                ['email' => $email],
                ['phoneNumber' => $phoneNumber],
            ])->limit(1)->one();

        if ($model) {
            $model->accountId = $accountId;
            $model->save(false);
        }
    }
}
