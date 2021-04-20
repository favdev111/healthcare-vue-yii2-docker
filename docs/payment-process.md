Payment Process
=======

According to changes in task https://heytutor.atlassian.net/browse/HT-930 
there are 3 types of payment process. Current payment process of company described in **'paymentProcessType'** field 
(**account** table). Lessons created with one of clients of current company will be processed using one of the three payment processes.
Constants with possible values for **'paymentProcessType'** described in application/modules/account/models/Account.php
and starts from 'PAYMENT_TYPE'

Possible payment process types
----
('paymentProcessType' field in 'account' table)
```php
const PAYMENT_TYPE_USUAL = 0;
const PAYMENT_TYPE_BATCH_PAYMENT = 1;
const PAYMENT_TYPE_PLATFORM_ACCOUNT = 2;
```

Possible transaction types
---
('type' field in 'transaction' table)
```php
const STRIPE_CAPTURE = 1;
const STRIPE_CHARGE = 2;
const STRIPE_REFUND = 3;
const STRIPE_TRANSFER = 4;
const PARTIAL_REFUND = 5;
```
Possible transaction object types
----
```php
const TYPE_ACCOUNT = 1;
const TYPE_LESSON = 2;
const TYPE_BACKGROUNDCHECKREPORT = 3;
const TYPE_CLIENT_BALANCE_AUTO = 4;
const TYPE_CLIENT_BALANCE_MANUAL_CHARGE = 5;
const TYPE_CLIENT_BALANCE_POST_PAYMENT = 6;
const TYPE_LESSON_BATCH_PAYMENT = 7;
```
Possible transaction statuses 
----
('objectType' field in 'transaction' table)
```php
const STATUS_NEW = 0;
const STATUS_SUCCESS = 1;
const STATUS_ERROR = 2;e
const STATUS_WAITING_FOR_APPROVE = 3;
const STATUS_REJECTED = 4;
const STATUS_PENDING = 5;
```


Regular payment process 
---- 
(PAYMENT_TYPE_USUAL)

1) If client fills balance - funds will be charged from client card to company stripe account.
In transaction table you can see this transactions with objectType = 4,5,6. 
More details about transaction objectTypes here application/modules/payment/models/Transaction.php see constant
 which begin from "TYPE_"
2) After the lesson new transaction with objectType = 2 (lesson) will be created. This transaction will be processed via queue.
If lesson with usual student - he will be charged.
In case lesson with client of company: company will be charged.
3) In case of lesson refund: transaction type ('type' field in 'transaction' table) will be changed from 2 (charge) to 3 (refund)
4) Client balance transaction could be refunded partially. New transaction will be created with type partial refund (5).
Even full refund of client balance transaction will be processed as partial refund (new transaction will be created).
5)Transactions with errors could be re-created using "re-charge" button. New transaction, related to transaction with error via **'parentId'**
field, will be created.

Group payment process 
------
(PAYMENT_TYPE_BATCH_PAYMENT)

Main idea: Make only one charge from company for all lessons. 
1) Balance refill process the same as in **'Regular payment process'**.
2) After the lesson new transaction with objectType = 7 and status = 0 (new) will be created.
 This transaction **will not** be processed via queue.
 To run group charge for all companies on the platform use  'payment/payment/process-group-transactions' command.
 It should calculate sum if lesson amount for all company clients and charge company. 
 New transaction with type = 8 will be created for each company.
 After that, all processed lessons transactions will be connected to previous charge via field **'groupTransactionId'** and processed.
 Here lesson transaction - it is transfer funds from main platform stripe account to tutor's stripe account.
 Status of all lesson transactions will be changed to pending. When transfer is completed and tutor received his funds,
  platform receives a webhook **'balance.available'** with tutor stripe account id. For this tutor, using queue, will be launched
  check of all his transfers. If transfer is complete - status of this transfer in our DB will be changed to success.
3) Lesson refund should be processed in 2 steps. First: reverse transfer of lesson transaction (transfer from platform to tutor's account).
Second: Partial refund of group transaction (objectType = 8) to return funds from main platform account to company stripe account.
Group charge transaction (objectType = 8) could not be refunded manually.
4) Client balance refund process the same as in **'Regular payment process'**.
5) Lesson transfer same as group transaction charge could be re-created using "re-charge" button on backend.

Platform account payment process
----
(PAYMENT_TYPE_PLATFORM_ACCOUNT)

In general - similar to **Regular payment process**. Differences:

1) In case of balance refill funds destination is main platform stripe account (not company stripe account as in regular case) 
2) After lesson with company client, new transaction with objectType = 2 AND type = 4 (STRIPE_TRANSFER) will be created and process during new Payment Process ```(look to \common\components\StripePlatformAccount::runPaymentProcess())```.
This transaction describes transfer funds from main platform to tutor's stripe account.
3) Lesson transfer could be refunded using reverse transfer process.
4) In case of error transaction could be re-created automatically. 

Entities:

**Payment Process**: describes single payment process. Look to ```application/modules/payment/models/PaymentProcess.php```.
 
**ProcessedLessonTransfers**:Payment process has related transfers (which have been processed during this payment process).
  ```application/modules/payment/models/ProcessedLessonTransfer.php``` - describes relation between payment process and transfer.
  
**Platform payout**: ```application/modules/payment/models/PlatformPayout.php``` - describes single payout from main platform account.
When payout created - save all payout data to ```platform_payouts``` table in Data Base with status "Pending".
 Status will be automatically changed to "Success" or "Error" using webhooks.
 
 **Important!** Webhooks `payout.failed` and ```payout.paid``` must be enabled in Stripe account setting. 
 

Deploy process (how to change payment process from PAYMENT_TYPE_BATCH_PAYMENT to PAYMENT_TYPE_PLATFORM_ACCOUNT):
 1. Deploy functionality
 
 2. Set flag **receivePaymentsToPlatformAccount** in **account** table to start receive funds to main platform account
  
 3. When there are enough funds in main platform account remove from cron task related to group payments.
 
 4. Manually start last group payment
 
 5. Check lesson transfers. There must not be "not approved" or failed lesson transfers.
 
 6. Configure stripe account webhooks `payout.failed` and ```payout.paid``` - enable only for main platform account  
 
 7. For target company set in account table paymentProcessType field value “2“
 
 8. Start command to create first row in payment_process table.
 ```yii stripe-main-platform/create-first-payment```
  
 9. Set up cron for command at 5-6pm LA. 00 - 01AM UTC
 ```yii stripe-main-platform/payment-process```
 
 Tables related to this payment process:
 ---
 
 payment_process
 ----
 ```
id - unsigned int 
date - type date - describes date start of this payment process
hasErrors - tinyint(1) - is there any erorrs during payment process
isNotEnoughFunds - tinyint(1) - is there any transfer's errors with reason "insufficient funds"   
earnedToday - float unsigned - how much money platform received today
paidToday - float unsigned - how much money platform spent today for lesson transfers
status - smallint(6) unsigned - is process has just been started and now in progress (STATUS_CREATED = 0) or process has been finished (STATUS_COMPLETE = 1)
availableBalanceAfterPaymentProcess - float unsigned - state of platform available balance after payouts and lesson transfers.
error - text - in case of Exception contains error message and trace.
``` 
 
 platform_payouts
 ---
 ```
id - usigned int
paymentProcessId - usigned int - describes related payment process, where this payout was created.
createdAt - dateTime
updatedAt - dateTime
status - current payout status. "Pending" after start payout process, then it will be updated via webhooks.
response - json - stripe response for request to create this payout
stripeId - varchar - contains id of payout in Stripe system
amount - float unsigned - payout amount
source - smallInt(6) unsigned - describes source wich was user for creating payout (bank_account or card). More info here https://stripe.com/docs/api/payouts/create#create_payout-source_type 
```

processedLessonsTransfers
---
```
paymentProcessId - usigned int - describes related payment process
lessonTransferId - usigned int - transfer related to payment process from field paymentProcessId
```
 Log file
 ---
 Each payment process will be described in ```application/console/runtime/logs/mainPlatformPayments.log```
 
 Console controller
 ---
 ``application/console/controllers/StripeMainPlatformController.php``
 
 Component for this payment process 
 ---
 ``application/common/components/StripePlatformAccount.php``

Payout cases
---
Available balance has 2 source_types: **bank_account** and **card**. Sum of all balances - it is total platform available balance.
Source type should be provided each time we create a payout. That's why there are some possible issues.

Logic of payout process described here: ```\common\components\StripePlatformAccount::processPayout()```

**Example:**

```
Total available balance = 1000
Card = 600
Bank account = 400.

Trying to create a payout with sum 700. Using payout with any source type we will receive an error.
In this case we should create 2 payouts.
Card source has priority.
So we create first payout with source type "Card" with amount 600 and another one payout with source type BA with amount 100.
```

Testing payout
---
You must have real BA connected to you main stripe to make possible platform payouts in test mode.  