#Tutor Scores
Types:
---

###Static (could be calculated any time):

*Profile content* - depends on a tutor's profile description size

*Response time* - depends on a tutor's average response time calculated in Cron task `php yii account/rating-account/weekly-response-time` 

*Tutoring hours* - depends on total lesson time (lesson duration)   

*Rating* - depends on a tutor's total rating (stars)

*HoursPerRelation* - depends on a tutor's total rating (stars)

###Dynamic (could be calculated only during search process):

*Most recent activity* - depends on difference between current search timestamp and stored tutor's last visit timestamp

*Distance* - depends on distance between a tutor's zip and zip provided to search model 

*Availability* - actual for search related to jobs. It depends on the match of the job's availability and the tutor's availability. 

###Automatch only

*Automatch scores (quiz)* - specific tab, contains additional bonuses for each job application

*Rematches per match* - count tutor rematches / count tutor hires. Multiple rematches per match will be calculated as 1 rematch.

*Hours per subject* - calculates count tutoring hours using subject from Job.


Configuration:
----

Route `/backend/account/tutor-score/index/`

On provided route you can set up `keys` and `values` for each score factor

`key` - contains value of score factor (distance in miles for example)

`value` - count scores in case score factor equal `key` 

Example : `key = 100, value = 10`, means if score factor value equal 100 - 10 points will be added. 

Use `-` symbol in `key` to set up range of keys. Example `key = 100-200, value = 10` means if key value between 100 and 200, 10 point will be added to score.

Use `+` to set up `>=` condition. Example `key=200+, value = 100` means: if score factor value more than 200 or equal 200 - 10 points will be added.

Elastic Search scripts
---
For static score factors based on Mysql data (table depends on a factor). For dynamic score scripts there are some ElasticSearch scripts.

Look for ``DISTANCE_SCORE_SCRIPT, TUTOR_AVAILABILITY_SCORE_SCRIPT, LAST_VISIT_SCORE_SCRIPT`` in `common/helpers/TutorSearchHelper.php`  
 
Usage
-----

1) `New job posted` notifications. 
`\modules\account\controllers\console\JobController::actionNewJob()`

2) Landings HT and PT (without availability)
`\frontend\controllers\SiteController::cityIndexExtraDataCallback()`
 
3) Book tutor landing(without distance and availability). Route `/tutors/`.
`\modules\account\controllers\frontend\BookTutorController::actionLanding()`

4) Landing tutors script (without availability) `\modules\seo\controllers\console\LandingPageController::actionFillTutorsForIndexedPages()`