Book Tutor modal
=======
Platform updates
----
1) update DB using migrations

2) use ```tutor/set-booking-company {id}``` command to set company related to booking modal. We will be looking for students related to these company and create new students related to these company.

3) for tests or some special cases there are 2 additional commands were added
``
tutor/check-booking-task {tutor_booking_id}
`` - creates queue job to check booking data with 0 duration. Same job will be created after step 3, but with 1h duration.
``
tutor/create-client-task {tutor_booking_id}
`` - creates queue job to create client account using booking data. Same job will be created after step 5.

``tutor/set-book-notification-emails email1 email2 emailN``  - use it to set emails (notification with booking information on step 5) 

Details
---
Endpoint **/book-tutor**
Example: ``dev.local/book-tutor``

Required **query** parameter ``step`` possible values from 1 to 5

**After step 3** job **modules/task/queueJobs/CheckBookingJob.php** will be added to queue with 1h duration.
If in 1h user will not have any added credit cards - new row will be added to {{lead}} table. This lead will be sent to saleforce. 

**After step 5** job **modules/task/queueJobs/CreateAccountFromBookingJob.php** will be added to queue.
This job must create a new client account related to HT company, using data from {{tutor_bookings}} table.
 
**After step 5** job **\modules\task\queueJobs\MailerJob** will be added to queue.
This job creates mails to addresses which were set using **tutor/set-book-notification-emails** command and provides booking information.
 
 In case of error row in **lead.log** will be added.  

Validation on each step
--- 

**Step 1:**
```php
tutorId
firstName
lastName
email
phoneNumber
gclid
```

After first step row in ``tutor_bookings`` will be created.
 Id of created row will be returned in ``tutorBookingId`` field.  
 This value required fi all other validation steps. If account is already exists field ```accountId``` will be filled in ``tutor_bookings``  table.
 
 
 **Step 2:**
 
 Required fields:
 
 ```php
tutorBookingId
subjects
schoolGradeLevelId
note
zipCode - (default value)
```

**Step 3:**
 
 Required fields:
 
 ```php
tutorBookingId
startDate
duration
```


**Step 4:**
 
 Required fields:
 
 ```php
tutorBookingId
hourlyRate
```

**Step 5:**
 
 Required fields:
 
 ```php
hourlyRate
tutorBookingId
paymentAdd
zipCode
```

Autofilled fields
-----
```php
ip - current use IP,
advertisingChannel - using same mechamism as in lead wizard,
bookingCompanyId - id of company which were set using tutor/set-booking-company {id} command
source - from referer as in lead wizard modal

```

/tutors/ page
-----
**View:** themes/basic/modules/account/views/frontend/book-tutor/landing.php

**Action:** \modules\account\controllers\frontend\BookTutorController::actionLanding()

Separate page with subject search, which displays list of tutors with profile links.

book-tutor/payment/
----
**View:** themes/basic/modules/account/views/frontend/book-tutor/payment.php

**Action:** \modules\account\controllers\frontend\BookTutorController::actionPayment()

Separate page, use it to refill step 4 (hourlyRate) and fill step 5 (payment information and zip).

**tutorBookingId** - required parameter in a query string. **tutorBookingId** should be id of the row from **{{tutor_bookings}}** table.
 Allowed to provide only bookings with step 4.

Update August 2020
=======
Task https://app.clubhouse.io/heytutor/story/336/adjustments-to-online-landing-page

**Duration** field removed from modal and from validation.

**TimePreferences** field added to modal instead of Duration select, field added to `tutor_bookings` table

**Rate** value provided from front always equal '59'. Packages removed from modal and payment pages.

**Automatic charge** disabled for all cases. 