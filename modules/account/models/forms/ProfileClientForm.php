<?php

namespace modules\account\models\forms;

use common\components\behaviors\ChildModelErrorsBehavior;
use common\components\HtmlPurifier;
use modules\account\behaviors\AdditionalDataBehavior;
use modules\account\models\AccountNote;
use modules\account\models\api\AccountClient;
use modules\account\models\api\AccountEmail;
use modules\account\models\api\AccountPhone;
use modules\account\models\api\ProfileClient;
use modules\account\models\Grade;
use modules\account\models\GradeItem;
use modules\account\models\Role;
use modules\account\models\SubjectOrCategory\AccountSubjectOrCategory;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use modules\chat\Module;
use modules\payment\models\CardInfo;
use Yii;
use yii\base\Model;

/**
 * Profile company client form
 * @property $phoneNumbers
 * @property $emails
 * @property int $gradeId
 */
class ProfileClientForm extends Model
{
    public $firstName;
    public $lastName;
    public $placeId;
    public $hourlyRate;
    public $subjects;
    public $startDate;
    public $zipCode;
    public $address;
    public $gradeId;

    public $childrenData;
    public $flag;


    const GRADE_LEVEL_ID_ADULT = 5;
    /**
     * @var string For internal use
     */
    public $dateOfBirth;

    /**
     * @var string Note
     */
    public $note;

    /**
     * @var array List of tokens to add
     */
    public $paymentAdd = [];

    /**
     * @var array List of ids to remove
     */
    public $paymentRemove = [];

    /**
     * @var string Set active payment card. Can be token or id
     */
    public $paymentActive;

    protected $attributesNameLoaded = [];

    /**
     * @var \modules\account\Module
     */
    public $module;

    /**
     * @var \modules\account\models\Account
     */
    public $accountCompanyModel;

    /**
     * @var \modules\account\models\Account
     */
    public $accountModel;

    public $schoolGradeLevelId;

    public $createdIp;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->module = Yii::$app->getModuleAccount();
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'create' => ['phoneNumbers', 'emails', 'firstName', 'lastName', 'placeId', 'hourlyRate', 'subjects', 'paymentAdd', 'note', 'paymentActive', 'childrenData', 'flag', 'startDate', 'gradeId',],
            'book' => ['phoneNumbers', 'emails', 'firstName', 'lastName', 'subjects', 'note', 'startDate', 'paymentAdd', 'zipCode', 'schoolGradeLevelId', 'hourlyRate', 'createdIp', 'gradeId',],
            'update' => ['phoneNumbers', 'emails', 'firstName', 'lastName', 'placeId', 'hourlyRate', 'subjects', 'paymentAdd', 'paymentRemove', 'paymentActive', 'childrenData', 'flag', 'startDate','gradeId', 'zipCode', 'address'],
        ];
    }


    /**
     * Check flag. Can client with this flag be assigned to employee or not;
     * @return bool
     */
    public function checkAssignPossibility(): bool
    {
        return AccountClient::isFlagRelatedToEmployee($this->flag);
    }


    public function behaviors()
    {
        return [
            'childModelErrors' => ChildModelErrorsBehavior::class,
            'additionalData' => [
                'class' => AdditionalDataBehavior::class,
                'accountEmailsClass' => AccountEmail::class,
                'accountPhonesClass' => AccountPhone::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules =  array_merge(
            ProfileClient::rulesCommon(),
            [
                [['placeId', 'phoneNumbers', 'emails', 'firstName', 'lastName', 'hourlyRate', 'schoolId', 'subjects'], 'required', 'on' => ['create']],
                [['phoneNumbers', 'emails', 'firstName', 'lastName', 'subjects'], 'required', 'on' => ['book']],
                [['hourlyRate'], 'double', 'min' => 20, 'max' => 250],
                [['subjects'], 'required', 'isEmpty' => function ($value) {
                    return empty($value);
                }, 'message' => 'You should have at least 1 subject.', 'on' => 'create'
                ],
                [['firstName', 'lastName', 'placeId', 'note'], 'filter', 'filter' => function ($value) {
                    return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
                }
                ],
                [
                    ['gradeId'],
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => Grade::class,
                    'targetAttribute' => ['gradeId' => 'id']
                ],
                [['note'], 'string', 'max' => 5000],
                [['paymentActive'], 'string'],
                [['paymentAdd'], 'each', 'rule' => ['string']],
                [['paymentAdd'], function ($attribute) {
                    $count = 0;
                    $paymentCustomerModel = $this->accountCompanyModel->paymentCustomer;
                    if ($paymentCustomerModel) {
                        $count = $paymentCustomerModel->getCardInfo()->count();
                    }

                    if (is_array($this->$attribute)) {
                        $count += count($this->$attribute);
                    }

                    if ($count > 10) {
                        $this->addError($attribute, 'You can add only 10 credit cards');
                    }
                },
                    'on' => ['create', 'update'],
                ],
                [['paymentRemove'], 'each', 'rule' => ['string']],
                [['childrenData'], 'safe'],
                [['flag'], 'string'],
                [['flag'], 'in', 'range' => AccountClient::getFlagNames()],
            ]
        );

        return array_merge($rules, AdditionalDataBehavior::validationRules());
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        $this->attributesNameLoaded = [];
        if (is_array($values)) {
            $attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                    $this->attributesNameLoaded[] = $name;
                } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                }
            }
        }
    }

    /**
     * Get values of loaded attributes
     *
     * @return array
     */
    protected function getAttributesValueLoaded()
    {
        $attributes = [];
        foreach ($this->attributesNameLoaded as $attribute) {
            $attributes[$attribute] = $this->$attribute;
        }

        return $attributes;
    }

    protected function addPaymentCards($account)
    {
        foreach ($this->paymentAdd as $cardToken) {
            if (empty($cardToken)) {
                continue;
            }
            Yii::$app->payment->attachCardToCustomer(
                $cardToken,
                $account,
                in_array($this->paymentActive, $this->paymentAdd)
            );
        }
    }

    protected function removePaymentCards($account)
    {
        if (
            empty($this->paymentRemove)
            || !is_array($this->paymentRemove)
        ) {
            return;
        }

        $models = CardInfo::find()->joinWith(['paymentCustomer' => function ($query) use ($account) {
            $query->andWhere(['accountId' => $account->id]);
        }
        ])
            ->andWhere(['in', CardInfo::tableName() . '.id', $this->paymentRemove])
            ->all();

        foreach ($models as $model) {
            Yii::$app->payment->removeCard($model->id, $this->accountModel);
        }
    }

    protected function addNote($account)
    {
        if (empty($this->note)) {
            return;
        }

        $noteModel = new AccountNote();
        $noteModel->accountId = $account->id;
        $noteModel->content = $this->note;
        $noteModel->save(false);
    }

    protected function updateSubjects($account)
    {
        if (empty($this->subjects)) {
            return;
        }

        $accountSubjectsOrCategories = $account->getAllAccountSubjectsOrCategories();
        if (!empty($accountSubjectsOrCategories)) {
            foreach ($accountSubjectsOrCategories as $model) {
                $model->delete();
            }
        }
        foreach ($this->subjects as $subjectOrCategoryId) {
            $accountSubjectOrCategory = new AccountSubjectOrCategory();
            $accountSubjectOrCategory->subjectId = (int)$subjectOrCategoryId;
            $accountSubjectOrCategory->isCategory = SubjectOrCategory::isIdOfCategory($subjectOrCategoryId);
            $accountSubjectOrCategory->accountId = $account->id;
            $accountSubjectOrCategory->description = '';
            $accountSubjectOrCategory->save(false);
        }
    }

    protected function updateGrade($account)
    {
        if (in_array('gradeId', $this->attributesNameLoaded)) {
            /**
             * @var GradeItem $gradeItem
             */
            $gradeItem = $account->profile->gradeItem;
            if (empty($gradeItem)) {
                $gradeItem = $account->profile->createGradeItem();
            }

            if (is_null($this->gradeId)) {
                $gradeItem->delete();
            } else {
                $gradeItem->gradeId = $this->gradeId;
                $gradeItem->save(false);
            }
        }
    }

    protected function addHourlyRate($account)
    {
        $accountRate = $this->module->model('AccountRate');
        $accountRate->accountId = $account->id;
        $accountRate->hourlyRate = $this->hourlyRate;
        $accountRate->save(false);
    }


    protected function updateHourlyRate($account)
    {
        if (empty($this->hourlyRate)) {
            return;
        }
        $accountRate = $account->rate;
        $accountRate->hourlyRate = $this->hourlyRate;
        $accountRate->save(false);
    }

    /**
     * Create company account
     */
    public function create()
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            /**
             * from application/modules/account/behaviors/AdditionalDataBehavior.php
             * @var AccountEmail $primaryEmail
             */
            $primaryEmail = $this->primaryEmailModel;
            $account = new AccountClient();
            $account->email = $primaryEmail->email;
            $account->roleId = Role::ROLE_PATIENT;
            $account->status = AccountClient::STATUS_ACTIVE;
            $account->isEmailConfirmed = true;
            if ($this->createdIp) {
                $account->createdIp = $this->createdIp;
            }
            $account->childrenData = $this->attributes['childrenData'];
            $account->flag = $this->attributes['flag'];

            $account->save(false);


            if ($account->hasErrors()) {
                throw new \Exception('Internal error');
            }

            /**
             * from application/modules/account/behaviors/AdditionalDataBehavior.php
             * @var AccountPhone $primaryPhomeNumberModel
             */
            $primaryPhoneNumberModel = $this->phoneNumberPrimaryModel;
            $profile = new ProfileClient();
            $profile->load(array_merge($this->attributes, ['phoneNumber' => $primaryPhoneNumberModel->phoneNumber, 'schoolGradeLevelId' => $this->schoolGradeLevelId]), '');
            $profile->accountId = $account->id;
            $profile->save(false);


            if ($profile->hasErrors()) {
                throw new \Exception('Internal error');
            }

            $this->updateGrade($account);

            $this->updateSubjects($account);
            if ($this->hourlyRate) {
                $this->addHourlyRate($account);
            }

            $this->addNote($account);

            /**
             * from application/modules/account/behaviors/AdditionalDataBehavior.php
             */
            $this->saveEmails(true, $account);
            $this->savePhones(true, $account);

            /**
             * @var Module $chat
             */
            $chat = Yii::$app->getModule('chat');
            if (!$chat->addUser($account)) {
                throw new \Exception('Failed to create chat user account');
            }

            Yii::$app->payment->createPaymentCustomer($account);
            if ($this->paymentAdd) {
                $this->addPaymentCards($account);
            }

            $transaction->commit();

            return $account;
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n" . $e->getTraceAsString();
            $transaction->rollBack();
            $this->addError('event', $e->getMessage());
        }

        return null;
    }


    /**
     * @return AccountClient|void
     * @throws \yii\db\Exception
     */
    public function update()
    {
        if (!$this->validate()) {
            return;
        }

        /** @var AccountClient $account **/
        /** @var ProfileClient $profile **/
        $account = $this->accountModel;
        $profile = $account->profile;
        $attributes = $this->getAttributesValueLoaded();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            /**
             * from application/modules/account/behaviors/AdditionalDataBehavior.php
             * @var AccountPhone $primaryPhomeNumberModel
             */
            $primaryPhoneNumberModel = $this->phoneNumberPrimaryModel;
            $profile->load(array_merge($attributes, ['phoneNumber' => $primaryPhoneNumberModel->phoneNumber ?? null]), '');
            $profile->save(false);

            if ($profile->hasErrors()) {
                $this->collectErrors($profile);
                return;
            }

            /**
             * from application/modules/account/behaviors/AdditionalDataBehavior.php
             * @var AccountEmail $primaryEmail
             */
            $primaryEmail = $this->primaryEmailModel;
            $loadArray = [
                'email' => $primaryEmail->email,
            ];

            if (isset($attributes['flag'])) {
                $loadArray['flag'] = $attributes['flag'];
            }

            if (isset($attributes['childrenData'])) {
                $loadArray['childrenData'] = $attributes['childrenData'];
            }

            $account->load($loadArray, '');

            if (
                ($primaryEmail->email
                    && $account->isAttributeChanged('email')
                    && $account->validate(['email']))
                || $this->childrenData || $account->isAttributeChanged('flag')
            ) {
                $account->save(false);
            }

            if ($account->hasErrors()) {
                $this->collectErrors($account);
                return;
            }

            $this->updateGrade($account);
            $this->updateSubjects($account);
            $this->updateHourlyRate($account);
            $this->removePaymentCards($account);
            $this->addPaymentCards($account);

            /**
             * from application/modules/account/behaviors/AdditionalDataBehavior.php
             */
            $this->saveEmails(false, $account);
            $this->savePhones(false, $account);


            if (
                !empty($this->paymentActive)
                && is_numeric($this->paymentActive)
            ) {
                Yii::$app->payment->setActiveCard($this->paymentActive, $this->accountModel);
            }

            $transaction->commit();

            return $account;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('event', $e->getMessage());
        }
    }
}
