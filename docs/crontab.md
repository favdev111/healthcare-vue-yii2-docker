Crontab
=======

Timezone on server: UTC

Timezone of crontabs: PDT (Pacific Daylight Time)

```
3 12 * * * php yii payment/payment/tutor-transfer
30 0 * * * php yii account/account/missing-data
1 * * * * php yii account/rating-account/set-student-hours
2 * * * * php yii account/rating-account/activate-pending-reviews
35 2 * * 1 php yii account/rating-account/weekly-response-time
10 0-6,16-23 * * * php yii account/job/new-job
20 12 * * * php yii account/account/clear-old-start-dates
35 14 * * 0,3,5 php yii seo/landing-page/fill-tutors-for-indexed-pages
4 14,21 * * * php yii account/elasticsearch/update-tutors-scores 1
45 12 * * 2,5 php yii location/download-ip-db
30 8 * * * php yii stripe-main-platform/payment-process
1 17,3 * * * php yii sms-message/send-tutor-hired
3 * * * * php yii sms-message/set-no-user-response-status
0 8 * * * php yii files/check
```
