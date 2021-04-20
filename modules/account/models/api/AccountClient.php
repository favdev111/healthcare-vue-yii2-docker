<?php

namespace modules\account\models\api;

use common\components\presenter\dto\LabelDTO;
use common\models\PostPayment;
use common\models\ClientChild;
use modules\account\models\AccountClientStatistic;
use modules\account\models\SubjectOrCategory\AccountSubjectOrCategory;
use modules\labels\models\LabelRelationModel;
use modules\payment\components\Payment;
use modules\payment\models\api\Transaction;
use Yii;
use yii\base\NotSupportedException;

/**
 * @inheritdoc
 *
 * @property EmployeeClient $employeeClient
 */
class AccountClient extends \modules\account\models\Account
{
    public $token;
    public $lastMessage;

    public $childrenData;
    public $children;

    const FLAG_RED = 'Red';
    const FLAG_YELLOW = 'Yellow';
    const FLAG_PURPLE = 'Purple';
    const FLAG_ORANGE = 'Orange';
    const FLAG_BLUE = 'Blue';
    const FLAG_LIGHT_BLUE = 'Light Blue';
    const FLAG_GREEN = 'Green';

    //clients with this flag could be assigned to employee
    public static $employeeFlags = [self::FLAG_RED, self::FLAG_BLUE, self::FLAG_LIGHT_BLUE];

    public static $flagData = [
        [
            'name' => self::FLAG_GREEN,
            'label' => 'Welcome Call',
            'color' => "#01bb00",
        ],
        [
            'name' => self::FLAG_RED,
            'label' => 'Tutor Required',
            'color' => "#FA0000",
        ],
        [
            'name' => self::FLAG_YELLOW,
            'label' => 'Follow Up',
            'color' => "#F5E600",
        ],
        [
            'name' => self::FLAG_PURPLE,
            'label' => 'Rate Change',
            'color' => "#930DFE",
        ],
        [
            'name' => self::FLAG_ORANGE,
            'label' => 'Billing Issue',
            'color' => "#F49300",
        ],
        [
            'name' => self::FLAG_BLUE,
            'label' => 'Online Tutor',
            'color' => "#00A4E7",
        ],
        [
            'name' => self::FLAG_LIGHT_BLUE,
            'label' => 'Second lesson',
            'color' => "#ADD8E6",
        ],
    ];

    /**
     * Get list of available flags
     * @return array
     */
    public static function getFlagNames(): array
    {
        $names = [];
        foreach (static::$flagData as $flagItem) {
            $names[] = $flagItem['name'];
        }
        return $names;
    }

    public static function isFlagRelatedToEmployee($flag): bool
    {
        return in_array($flag, AccountClient::$employeeFlags);
    }



    public function rules()
    {
        return array_merge(parent::rules(), [
            [['flag'], 'string']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getProfile()
    {
        return $this->hasOne(ProfileClient::class, ['accountId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountEmails()
    {
        return $this->hasMany(AccountEmail::class, ['accountId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountPhones()
    {
        return $this->hasMany(AccountPhone::class, ['accountId' => 'id']);
    }

    public function getPostPayments()
    {
        return $this->hasMany(PostPayment::class, ['accountId' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $accountId = Yii::$app->user->id;
        if (!$accountId) {
            throw new NotSupportedException();
        }

        $query = parent::find()
            ->notEmployee();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findOneWithoutRestrictions($id)
    {
        return parent::find()->andWhere([self::tableName() . '.id' => $id])->one();
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = [
            'id',
            'email',
            'hourlyRate' => function () {
                return (double)$this->rate->hourlyRate;
            },
            'subjects' => 'subjectsOrCategories',
            'profile',
            'lastMessage',
            'chat' => function () {
                return $this->chat;
            },
            'paymentCustomerId' => function () {
                if (!$this->paymentCustomer) {
                    /**
                     * @var $paymentComponent Payment
                     */
                    $paymentComponent = Yii::$app->payment;
                    $paymentComponent->createPaymentCustomer($this);
                    unset($this->paymentCustomer);
                }
                return $this->paymentCustomer->id;
            },
            'statistic' => function () {
                $model = $this->clientStatistic;
                if (!$model) {
                    $model = new AccountClientStatistic();
                    $model->validate();
                }

                return $model;
            },
            'childrenData' => function () {
                return ClientChild::find()->with('grade')->andWhere(['accountId' => $this->id])->asArray()->all();
            },
            'flag',
            'flagDate',
            'clientInvited',
            'labels' => function () {
                $label = LabelRelationModel::find()
                    ->andWhere(['itemId' => $this->id])
                    ->one();

                return $label ? (new LabelDTO(
                    $label->labelId,
                    $label->label->name,
                    $label->label->color,
                    $label->label->categoryId,
                    $label->description,
                    $label->itemId,
                    $label->id
                ))->toArray() : null;
            }
        ];

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'payment' => 'cardInfo',
            'createdAt',
            'postPayments',
            'employeeClient',
            'accountEmails',
            'accountPhones',
        ];
    }


    protected function createChild($data)
    {
        $childModel = new ClientChild();
        $childModel->load($data, '');
        $childModel->accountId = $this->id;
        $childModel->save();
        if (!empty($data['gradeId'])) {
            $gradeItem = $childModel->createGradeItem();
            $gradeItem->gradeId = $data['gradeId'];
            $gradeItem->save(false);
        }
    }

    public function fillChildren()
    {
        if ($this->childrenData) {
            foreach ($this->childrenData as $childData) {
                $model = null;
                if (!empty($childData['id'])) {
                    $model = ClientChild::findById($childData['id']);
                }
                if (empty($model)) {
                    $this->createChild($childData);
                    continue;
                }
                if ($childData['isDeleted']) {
                    if (!empty($model->gradeItem)) {
                        $model->gradeItem->delete();
                    }
                    $model->delete();
                } else {
                    $model->load($childData, '');
                    $model->save();

                    //look for grade in db
                    $gradeItem = $model->gradeItem;
                    //in case gradeId provided for child
                    if (!empty($childData['gradeId'])) {
                        //create if there is no related row in db
                        if (empty($gradeItem)) {
                            $gradeItem = $model->createGradeItem();
                        }
                        //update value
                        $gradeItem->gradeId = $childData['gradeId'];
                        $gradeItem->save(false);
                    } elseif (!empty($gradeItem)) {
                        //otherwise if grade data not provided and related grade exists - delete grade
                        $gradeItem->delete();
                    }
                }
            }
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->fillChildren();
        parent::afterSave($insert, $changedAttributes);
    }

    public function getAllAccountSubjectsOrCategories()
    {
        return AccountSubjectOrCategory::find()->andWhere(['accountId' => $this->id])->all();
    }

    /**
     * Relation for client
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeClient()
    {
        return $this->hasOne(EmployeeClient::class, ['clientId' => 'id']);
    }

    /**
     * @param int|null $withinDays
     * @return \yii\db\ActiveQuery
     * @throws NotSupportedException
     */
    public static function getClientsWithLessonsQuery(int $withinDays = null): \yii\db\ActiveQuery
    {
        $withLessonsIds = static::find()
            ->select(static::tableName() . '.id');
        if ($withinDays) {
            //where lesson for period
            $withLessonsIds->hasLessonWithinDays($withinDays);
        } else {
            //or lesson exist
            $withLessonsIds->joinWith('studentLessons')->andWhere(['not', [Lesson::tableName() . '.id' => null]]);
        }
        return $withLessonsIds->distinct();
    }

    /**
     * @param int|null $withinDays
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    public static function getClientsWithPositiveClientBalances(int $withinDays = null): \yii\db\ActiveQuery
    {
        $dateTime = new \DateTime();
        $withPositiveClientBalanceIds = ClientBalanceTransaction::find()
            ->select(ClientBalanceTransaction::tableName() . '.clientId')
            ->leftJoin(
                Transaction::tableName(),
                \modules\account\models\ClientBalanceTransaction::tableName() .
                '.transactionId=' .
                \modules\payment\models\Transaction::tableName() . '.id'
            )
            ->andWhere([
                'or',
                [Transaction::tableName() . '.objectType' => Transaction::clientBalanceTypes()],
                [ClientBalanceTransaction::tableName() . '.transactionId' => null]
            ]);
        if ($withinDays) {
            $withPositiveClientBalanceIds->andWhere([
                '>=',
                ClientBalanceTransaction::tableName() . '.createdAt',
                $dateTime->sub(new \DateInterval('P' . $withinDays . 'D'))->format(\Yii::$app->formatter->MYSQL_DATE)
            ]);
        }
        return $withPositiveClientBalanceIds
            ->andWhere(['>', ClientBalanceTransaction::tableName() . '.amount', 0])
            ->distinct();
    }

    /**
     * @param int|null $withinDays
     * @return array - array with ids of client who has lesson or positive client balance transaction
     * @throws NotSupportedException
     */
    public static function getListOfActiveClients(int $withinDays = null): array
    {

        $withLessonsIds = self::getClientsWithLessonsQuery($withinDays)->column();
        $withPositiveClientBalanceIds = self::getClientsWithPositiveClientBalances($withinDays)->column();

        return array_unique(array_merge($withLessonsIds, $withPositiveClientBalanceIds));
    }
}
