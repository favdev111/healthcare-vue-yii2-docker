Auto - matching
======

OPS email
---
Constant `OPS_TEAM_EMAIL` added to .env

Automatch companies
-----
Only selected companies could use automatch functionality. 
Use `php yii account/job/set-automatch-companies {id-1} {id-2} {id-N}` command to set up these companies.
You can check current companies list in `settings` table.

Job Apply
---
**isOnlineBefore** - store answer for "Have you tutored online before?" question.

 After apply, count of **points** will be calculated, value depends on job_apply.description and job_apply.isOnlineBefore
 
 **automatchScore** - contains total number of points in automatch process, calculated after apply. 
 
 Bonus points could be changed via specific tab on "Tutor Scores" section on admin panel. 
 
 After applied new chat message  with job-apply description will be sent from tutor to client.
  
 Job 
 -----
 **isAutomatchEnabled** - was added to **job** table. Could be set via **jobs/** API (POST and PUT request).
 
 In POST request for common Job model, PUT and POST requests on API Job model: job's subjects will be compared with list "automatch subjects.". In case job contains at least 
 one of the "automatch subjects" - flag will be set to true.
 
 **automatchJobId** - contains id of created job from yii_queue table  
 
 Before new job added to DB `isAutomatchEnabled` will be checked.
 In case this is B2B job (related to company client) AND client's company - is automatch company 
 AND job has automatched subjects - `isAutomatchEnabled` will be set to `true` 
 
 In case job creation process if `isAutomatchEnabled` is equal to `true` new task will be added to queue.
 
 if only `isAutomatchEnabled` flag changed - job's updatedAt field will not be changed. 
 
 On front there is a tab "automatched" with list of jobs related to automatch functionality. Content depends on `isAutomatchEnabled` flag value
 and related row in `automatch_history` table. In case after match process no one was hired - job will be removed form the tab.
 In case tutor was hired - job's flag `isAutomatchEnabled` will be disabled and related row in  `automatch_history` will be created.
 
 ``IsAutomatchJob`` property was added to Job entity. logic: has enabled  `isAutomatchEnabled` flag OR has related row in automatch_history table.
 
 Automatch timer
 -----
 Job entity has dynamic property `automatchTimerEnd` field. Formula: yii_queue.pushed_at + 3h - currentTimestamp()
 Use this value to calculate timer on front.
 
 Automatch history
  -------
  Created table `automatch_history` which contains score details for each success match.
  
 
 Automatch queue task
 ------
 ``\modules\task\queueJobs\AutomatchJob``
 Looks for job;
 If job not found - exit;
 If job `isAutomatchEnabled` is equal 0 - exit;
 Look for JobHires with status `Hired(1)`. If at least one of them exists - exit;
 Look for not declined applies.
 Compare list of tutors from `job_apply` table and list JobHires with statuses `STATUS_DECLINED_BY_TUTOR (2)` AND `STATUS_DECLINED_BY_COMPANY(0)`.
 Compare calculated point for each applicant. Find one with the highest score. 
 Add row to `automatch_history` table with process details.
 Create job hire with status "Hired" for a selected tutor. 
 
 Automatch subjects list
 ----
 Available on `/backend/automatch-subjects/index/` route.
  
 
 Log file
 -----
 `common/runtime/console/automatch.log`
 
 Console command for tests
 ------
 `php yii account/job/start-automatch-job {jobId} {zeroDelay}`
 
 For selected job flag `isAutomatchEnabled` will be enabled, JobHires with status "Hired" - deleted.
 Old automatch task will be deleted from queue, new task will be created, job updated.
 if `{zeroDelay}` is equal to `1` - new automath task will be created without 3h delay.