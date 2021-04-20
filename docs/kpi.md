KPI LTV
=======

Search model for KPI statistic ```\modules\analytics\models\search\KpiSearch```

Cash basis
-------
Method ```KpiSearch::calculateCashBasis()```

**Return result:**

```$cashBasisTotal``` - cash basis for whole time. This value doesnt related to any of the filters
```$totalClientCount``` - count of clients who were used in ```cashBasisTotal``` calculation
```$averageCashBasisTotal``` - average value for each client in total selection
```$cashBasis``` - cash basis value which includes selected filters
```$clientCount``` - count of clients who were used in ```$cashBasis``` calculation
```$averageCashBasis``` - average value for each client in selection with filters
 
 if provided flag `additionalData` field `cashBasisClientListForPeriod` will be added to result. It contains id's of users which were used in selection with filters.
 
 
**Query steps:**

1. ```$activeClientListTotal``` - looking for clients who have at least 1 deposit or lesson
2. ```$activeClientsWithDepositTotal``` - using ```$activeClientListTotal``` find clients  who have at least 1 deposit or lesson and who made total deposit for 400$ or more
3. ```$activeClientsWithDepositTotalQuery``` - using results of step 1 and step 2, find clients who exist in both selections
4. Calculating ```$cashBasisTotal```
5. ```$activeClientWithDepositCondition``` - using ```$activeClientListTotal``` - find clients who have first client balance transaction in selected date range
6. ```$activeClientsWithDepositQuery``` - using ```$activeClientsWithDepositTotal``` and ```$activeClientWithDepositCondition``` find clients who exist in both selections
7. Calculating `cashBasis`

Accrual Basis
--------

Method ```KpiSearch::calculateAccrualBasis()```

**Return result:**

```accrualBasisTotal``` - cash basis for whole time. This value doesnt related to any of the filters
```accrualBasisClients``` - count of clients who were used in ```accrualBasisTotal``` calculation
```averageAccrualBasisTotal``` - average value for each client in total selection
```accrualBasis``` - cash basis value which includes selected filters
```accrualBasisClients``` - count of clients who were used in ```$cashBasis``` calculation
```averageAccrualBasis``` - average value for each client in selection with filters
 
 
**Query steps:**

1. ```activeClientListTotal``` - looking for clients who have at least 1 deposit or lesson
2. ```activeClientsWithExpensesTotal``` - using ```activeClientListTotal``` find clients  who have at least 1 deposit or lesson and spent at least 100$ on the platform 
3. ```activeClientsWithExpensesTotalQuery``` - using results of step 1 and step 2, find clients who exist in both selections
4. Calculating ```accrualBasisTotal```
5. ```$activeClientWitExpensesForPeriod``` - using ```activeClientListTotal``` - find clients who have first client balance transaction in selected date range
6. ``activeClientsWithExpenses``- using `$ctiveClientListTotal` as base find client who spent at least 100$ on the platform
7. `activeClientsWithExpensesQuery` - find client who exist on bath selections from steps 6 and 7 
8. Use selected subjects to calculate `accrualBasis`

Tutor to students Ratio
-----
Method `KpiSearch::getTutorToStudentRatio()`

Ratio between tutors who have lessons and student who have lessons. Find using ```lesson``` table. 