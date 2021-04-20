<?php

namespace modules\account\models\api\search;

use common\components\behaviors\FilterDatesBehavior;
use common\components\Formatter;
use modules\account\models\AccountReturn;
use modules\account\models\api\Account;
use modules\account\models\api\AccountEmployee;
use modules\account\models\api\ClientRefund;
use modules\account\models\api\ClientRematch;
use modules\account\models\api\JobHire;
use modules\account\models\RematchJobHire;
use modules\account\models\Role;
use yii\base\Model;
use yii\db\Expression;

class AccountEmployeeStatisticSearch extends Model
{
    public $id;
    protected $employeeAccount;
    public function behaviors()
    {
        return [
            'filterDates' => [
                'class' => FilterDatesBehavior::class,
            ],
        ];
    }

    public function rules()
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        $rules = [
            ['id', 'required'],
            ['id', 'integer'],
        ];

        $rules[] = [['dateFrom'], 'date', 'format' => 'php:' . $this->incomingDateFormat, 'skipOnEmpty' => true, 'min' => strtotime(AccountReturn::STATISTIC_DATE_START), 'minString' => date($formatter->dateWithSlashesPhp, strtotime(AccountReturn::STATISTIC_DATE_START))];
        $rules = array_merge($rules, $this->getFilterDatesRulesArray());
        return $rules;
    }

    public function getAccountModel()
    {
        if (empty($this->employeeAccount)) {
            $this->employeeAccount = Account::findWithoutRestrictions()
                ->andWhere(['roleId' => [Role::ROLE_COMPANY_EMPLOYEE]])
                ->andWhere(['id' => $this->id])
                ->limit(1)
                ->one();
        }
        return $this->employeeAccount;
    }

    public function search()
    {
        $this->load(\Yii::$app->request->queryParams, '');

        if (!$this->validate()) {
            return $this;
        }

        //all employee hires
        $allOwnHiresQuery = JobHire::find()
            ->andWhere(['responsibleId' => $this->getAccountModel()->id])
            ->activeOrWasActive()
            ->select('id');

        //looking for all hires made by this employee
        $jobHiresIds = clone $allOwnHiresQuery;

        // \common\components\behaviors\FilterDatesBehavior::filterDate()
        $this->filterDate($jobHiresIds, JobHire::tableName());
        //https://heytutor.atlassian.net/browse/HT-878
        $jobHiresIds->andFilterWhere(['>=', JobHire::tableName() . '.createdAt', AccountReturn::STATISTIC_DATE_START]);

        $jobHiresIds = $jobHiresIds->column();
        $totalCount = count($jobHiresIds);


        //looking for rematches
        $countRematchesQuery = RematchJobHire::find()
            ->select(RematchJobHire::tableName() . '.jobHireId')
            ->joinWith('accountReturn')
            ->andWhere([RematchJobHire::tableName() . '.jobHireId' => $allOwnHiresQuery])
            //rematches without $reasonsNotAffectedToStatistic
            ->andWhere([
                'and',
                ['not', ['reasonCode' => ClientRematch::$reasonsNotAffectedToStatistic]],
                [AccountReturn::tableName() . '.type' => AccountReturn::TYPE_REMATCH]
            ])
            ->groupBy(RematchJobHire::tableName() . '.jobHireId')
            ->distinct();

        $this->filterDate($countRematchesQuery, AccountReturn::tableName());
        $filterResult = RematchJobHire::find()
            ->joinWith('jobHire')
            ->joinWith('accountReturn')
            ->andWhere([RematchJobHire::tableName() . '.jobHireId' => $countRematchesQuery])
            ->andWhere(new Expression(JobHire::tableName() . '.createdAt >= DATE_SUB(' . AccountReturn::tableName() . '.createdAt, INTERVAL 30 DAY)'))
            ->select(RematchJobHire::tableName() . '.jobHireId')
            ->asArray()
            ->column();
        $rematchesIds = [];
        foreach ($filterResult as $item) {
            if (!in_array($item, $rematchesIds)) {
                $rematchesIds[] = $item;
            }
        }
        $countRematches = count($rematchesIds);

        if ($totalCount) {
            $percent = $countRematches / $totalCount * 100;
        } else {
            $percent = 0;
        }

        $refundsIdsQuery = AccountReturn::find()
            ->refunds()
            ->byEmployeeId($this->getAccountModel()->id);
        $this->filterDate($refundsIdsQuery, AccountReturn::tableName());
        $refundsIds = $refundsIdsQuery->select('id')->column();

        $countRefunds = count($refundsIds);

        if ($totalCount) {
            $percentRefund = $countRefunds / $totalCount * 100;
        } else {
            $percentRefund = 0;
        }

        $data['rematchPercent'] = $percent;
        $data['totalCount'] = $totalCount;
        $data['countRematches'] = $countRematches ?? 0;
        $data['createdJobHiresIds'] = $jobHiresIds;
        $data['rematchedJobHiresIds'] = $rematchesIds;
        $data['refundIds'] = $refundsIds;
        $data['countRefunds'] = $countRefunds;
        $data['refundPercent'] = $percentRefund;
        return $data;
    }
}
