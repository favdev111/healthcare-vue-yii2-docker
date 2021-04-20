<?php

namespace modules\account\models\api;

use modules\account\models\query\AccountQuery;
use modules\account\models\RematchJobHire;
use modules\account\models\Team;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;

/**
 * Class AccountEmployee
 * @property int $countClients
 * @property-read float $averageMargin
 * @property-read array $employeeClients
 * @property AccountTeam $accountTeam
 * @package modules\account\models\api
 */
class AccountEmployee extends \modules\account\models\Account
{
    /**
     * @var
     */
    protected $margins;
    protected static $accountTeamClass = AccountTeam::class;
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'email',
            'status',
            'profile',
            'roleId',
            'countClients',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['status'], 'integer']
        ]);
    }

    /**
     * @return array|false
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['employeeClients'] = 'employeeClients';
        $extraFields['averageMargin'] = 'averageMargin';
        $extraFields['team'] = 'team';
        $extraFields['accountTeam'] = 'accountTeam';
        return $extraFields;
    }

    /**
     * @return int|string
     */
    public function getCountClients(): int
    {
        return (int)$this->getEmployeeClients()->count();
    }

    /**
     * @return float
     */
    public function getAverageMargin(): float
    {
        if (empty($this->margins)) {
            $totalMargins = 0;
            $countHires = 0;
            $jobHiresQuery = JobHire::find()
                ->with('job.account.company')
                ->andWhere(['responsibleId' => $this->id])
                ->activeOrWasActive();
            foreach ($jobHiresQuery->each() as $jobHire) {
                assert($jobHire instanceof \modules\account\models\JobHire);
                $totalMargins += $jobHire->margin;
                $countHires++;
            }
            if ($countHires) {
                $totalMargins /= $countHires;
            }
            $this->margins = $totalMargins;
        }
        return $this->margins;
    }

    /**
     * @return ActiveQuery
     */
    public function getEmployeeClients(): ActiveQuery
    {
        return $this->hasMany(EmployeeClient::class, ['employeeId' => 'id']);
    }


    /**
     * @param AccountQuery $query
     * @return AccountQuery
     */
    protected static function addNonDeletedCondition($query)
    {
        return $query->notDeleted();
    }
}
