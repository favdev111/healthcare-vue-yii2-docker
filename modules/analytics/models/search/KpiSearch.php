<?php

namespace modules\analytics\models\search;

use common\components\ActiveQuery;
use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\AccountSubject;
use modules\account\models\api\ClientBalanceTransaction;
use modules\account\models\api\Lesson;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use modules\payment\models\Transaction;
use yii\base\Model;
use yii\db\Query;

/**
 * Class KpiSearch
 * @package modules\analytics\models\search
 */
class KpiSearch extends Model
{
    public $subjectOrCategoryIds = [];
    public $additionalData = false;
    const MIN_DEPOSIT_FOR_CASH_BASIS = 400;
    const MIN_SPENDING_FOR_ACCRUAL_BASIS = 100;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            $this->getFilterDatesRulesArray(),
            [
                ['subjectOrCategoryIds', 'each', 'rule' => ['string']],
                ['additionalData', 'boolean']
            ]
        );
    }

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                FilterDatesBehavior::class,
            ]
        );
    }


    public function calculateCashBasis()
    {
        //total clients who have at least 1 deposit or lesson
        $activeClientListTotal = ClientBalanceTransaction::find()
            ->select(
                [
                    ClientBalanceTransaction::tableName()  . '.clientId',
                ]
            )
            ->groupBy(ClientBalanceTransaction::tableName() . '.clientId');

        //look for clients who made deposit for 400$ or more
        $activeClientsWithDepositTotal =
            ClientBalanceTransaction::setMoneyIncomeConditions(clone $activeClientListTotal)
                ->addSelect('SUM(' . ClientBalanceTransaction::tableName() . '.amount) as sumAmount')
                ->andHaving(['>=', 'sumAmount', static::MIN_DEPOSIT_FOR_CASH_BASIS]);


        //from total list of client select only clients who has deposit 400 and more
        $activeClientsWithDepositTotalQuery = (new Query())
            ->select('clientId')
            ->andWhere(
                [
                    'clientId' => (new Query())
                        ->select('clientId')
                        ->from(['clientsWithDeposits' => $activeClientsWithDepositTotal]),
                ]
            )
            ->from(['activeClient' => $activeClientListTotal]);


        //calculate cash basis for clients selected for all time with deposit more than 400$
        $cashBasisTotal = (float)ClientBalanceTransaction::getCashBasis($activeClientsWithDepositTotalQuery);

        //filtering clients with deposits or lesson charge by minDate (date of first client balance transaction)
        $activeClientWithDepositCondition = $this->filterDateHaving(
            (clone $activeClientListTotal)
                ->addSelect('MIN(' . ClientBalanceTransaction::tableName() . '.createdAt) as minDate'),
            '',
            true,
            'minDate'
        );

        //from total list of client select only clients who has deposit 400 and more
        $activeClientsWithDepositQuery = (new Query())
            ->select('clientId')
            ->andWhere(
                [
                    'clientId' => (new Query())
                        ->select('clientId')
                        ->from(['clientsWithDeposits' => $activeClientsWithDepositTotal]),
                ]
            )
            ->from(['activeClient' => $activeClientWithDepositCondition]);

        //calculate cash basis for filtered clients
        $cashBasis = ClientBalanceTransaction::getCashBasis($activeClientsWithDepositQuery);

        $totalClientCount = (int)$activeClientsWithDepositTotalQuery->count();
        $clientCount = (int)$activeClientsWithDepositQuery->count();

        $data =  [
            'cashBasisTotal' => $cashBasisTotal,
            'cashBasisTotalClients' => $totalClientCount,
            'averageCashBasisTotal' =>  $totalClientCount == 0 ? 0 : ($cashBasisTotal / $totalClientCount),
            'cashBasis' => $cashBasis,
            'cashBasisClients' => $clientCount,
            'averageCashBasis' =>  $clientCount == 0 ? 0 : ($cashBasis / $clientCount),
        ];

        if ($this->additionalData) {
            $data['cashBasisClientListForPeriod'] = $activeClientsWithDepositQuery->column();
        }

        return $data;
    }

    public function calculateAccrualBasis()
    {

        //total clients who have at least 1 deposit or lesson
        $activeClientListTotal = ClientBalanceTransaction::find()
            ->select(
                [
                    ClientBalanceTransaction::tableName()  . '.clientId',
                ]
            )
            ->groupBy(ClientBalanceTransaction::tableName() . '.clientId');

        //look for clients who spent 100$ or more
        $activeClientsWithExpensesTotal =
            ClientBalanceTransaction::setSpentMoneyConditions(clone $activeClientListTotal)
                ->addSelect('SUM(' . ClientBalanceTransaction::tableName() . '.amount) as sumAmount')
                ->andHaving(['>=', 'ABS(sumAmount)', static::MIN_SPENDING_FOR_ACCRUAL_BASIS]);

        //from total list of client select only clients spent at least 100$
        $activeClientsWithExpensesTotalQuery = (new Query())
            ->select('clientId')
            ->andWhere(
                [
                    'clientId' => (new Query())
                        ->select('clientId')
                        ->from(['clientsWithDeposits' => $activeClientsWithExpensesTotal]),
                ]
            )
            ->from(['activeClient' => $activeClientListTotal]);

        $accrualBasisTotal = ClientBalanceTransaction::getAccrualBasis(
            $activeClientsWithExpensesTotalQuery
        );

        //filtering clients with expenses by minDate (date of first client balance transaction)
        $activeClientWitExpensesForPeriod = $this->filterDateHaving(
            (clone $activeClientListTotal)
                ->addSelect('MIN(' . ClientBalanceTransaction::tableName() . '.createdAt) as minDate'),
            '',
            true,
            'minDate'
        );


        //look for clients who spent 100$ or more
        $activeClientsWithExpenses =
            ClientBalanceTransaction::setSpentMoneyConditions(clone $activeClientListTotal)
                ->addSelect('SUM(' . ClientBalanceTransaction::tableName() . '.amount) as sumAmount')
                ->andHaving(['>=', 'ABS(sumAmount)', static::MIN_SPENDING_FOR_ACCRUAL_BASIS]);

        //get subject ids from categories
        $selectedSubjectIds = SubjectOrCategory::convertToSubjectIds($this->subjectOrCategoryIds);

        $activeClientsWithExpensesQuery = (new Query())
            ->select('clientId')
            ->andWhere(
                [
                    'clientId' => (new Query())
                        ->select('clientId')
                        ->from(['clientsWithDeposits' => $activeClientsWithExpenses]),
                ]
            )
            ->from(['activeClient' => $activeClientWitExpensesForPeriod]);

        if ($this->subjectOrCategoryIds) {
            $subjectQuery = Lesson::find()
                ->select('studentId')
                ->andWhere(['subjectId' => $selectedSubjectIds]);

            $activeClientsWithExpensesQuery->andWhere(['clientId' => $subjectQuery]);
        }


        //calculate cash basis for filtered clients
        $accrualBasis = ClientBalanceTransaction::getAccrualBasis($activeClientsWithExpensesQuery, $selectedSubjectIds);

        $totalClientCount = (int)$activeClientsWithExpensesTotalQuery->count();
        $clientCount = (int)$activeClientsWithExpensesQuery->count();

        $data =  [
            'accrualBasis' => $accrualBasis,
            'accrualBasisClients' => $clientCount,
            'accrualBasisTotal' => $accrualBasisTotal,
            'accrualBasisTotalClients' => $totalClientCount,
            'averageAccrualBasis' =>  $clientCount == 0 ? 0 : ($accrualBasis / $clientCount),
            'averageAccrualBasisTotal' =>  $totalClientCount == 0 ? 0 : ($accrualBasisTotal / $totalClientCount),
        ];

        if ($this->additionalData) {
            $data['accrualBasisClientListForPeriod'] = $activeClientsWithExpensesQuery->column();
        }
        return $data;
    }

    public function getTutorToStudentRatio()
    {

        $activeClientsQuery = Lesson::find();
        $activeClientsQuery->select('studentId')
            ->groupBy('studentId')
            ->asArray();

        $activeTutorCount = (int)Lesson::find()->select('tutorId')
            ->groupBy('tutorId')
            ->asArray()
            ->andWhere(['studentId' => $activeClientsQuery])
            ->count();

        $activeClientCount =  (int)$activeClientsQuery->count();

        return [
            'tutorToStudentRatio' => ($activeClientCount == 0) ? 0 : $activeTutorCount / $activeClientCount,
            'activeTutorsCount' => $activeTutorCount,
            'activeClientsCount' => $activeClientCount,
        ];
    }

    /**
     * @param $params
     * @param $pageSize
     *
     * @return []
     */
    public function search($params)
    {
        $this->load($params, '');

        if (!$this->validate()) {
            return false;
        }

        return array_merge(
            $this->calculateCashBasis(),
            $this->calculateAccrualBasis(),
            $this->getTutorToStudentRatio()
        );
    }
}
