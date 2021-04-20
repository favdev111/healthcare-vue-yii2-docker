<?php

namespace modules\account\models\backend;

use modules\account\models\Account;
use modules\account\models\Role;
use Yii;
use yii\base\Model;

/**
 * EditRequiredForm is the model behind the tutor account edit requirements notifications.
 */
class EditRequiredForm extends Model
{
    public $tutorId;
    public $updateFields = [];

    const FIELD_TITLE = 0;
    const FIELD_DESCRIPTION = 1;
    const FIELD_PICTURE = 2;
    const FIELD_SUBJECTS = 3;

    public static function availableFields()
    {
        return [
            self::FIELD_TITLE => 'Title',
            self::FIELD_DESCRIPTION => 'Description',
            self::FIELD_PICTURE => 'Picture',
            self::FIELD_SUBJECTS => 'Subjects',
        ];
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['tutorId'], 'required'],
            ['updateFields', 'each', 'rule' => ['in', 'range' => array_keys(self::availableFields())]],
            ['tutorId', 'exist', 'targetClass' => Account::className(), 'targetAttribute' => 'id', 'filter' => function ($query) {
                return $query->andWhere(['roleId' => Role::ROLE_SPECIALIST]);
            }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tutorId' => 'Tutor',
            'updateFields' => 'Fields that require edits',
        ] + self::availableFields();
    }

    public function save($validate = true)
    {
        if ($validate && !$this->validate()) {
            return false;
        }
        // Create notification
        /**
         * @var $notificationModule \modules\notification\Module
         */
        $notificationModule = Yii::$app->getModule('notification');
        $notificationModule->accountEditRequired($this->tutorId, ['fields' => $this->updateFields]);
        $tutor = Account::findOne($this->tutorId);
        $tutor->status = Account::STATUS_EDIT_REQUIRED;
        return $tutor->save(false, ['status']);
    }

    public static function getFieldLink($field)
    {
        switch ($field) {
            case self::FIELD_TITLE:
                return ['/account/profile-tutor/edit-profile'];
                break;
            case self::FIELD_DESCRIPTION:
                return ['/account/profile-tutor/edit-profile'];
                break;
            case self::FIELD_PICTURE:
                return ['/account/profile-tutor/about-me'];
                break;
            case self::FIELD_SUBJECTS:
                return ['/account/profile-tutor/subjects'];
                break;
            default:
                return ['/account/profile-tutor/about-me'];
        }
    }

    public static function getFieldTitle($field)
    {
        switch ($field) {
            case self::FIELD_TITLE:
                return 'Update your title';
                break;
            case self::FIELD_DESCRIPTION:
                return 'Add more content to your description';
                break;
            case self::FIELD_PICTURE:
                return 'Update profile picture (headshots are preferred)';
                break;
            case self::FIELD_SUBJECTS:
                return 'Add specific subject content';
                break;
            default:
                return 'Complete your profile';
        }
    }
}
