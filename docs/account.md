Account
=======

Multiple data for clients
-----
3 tables were created: **account_phone**, **account_email**, **phone_validation**

Fields that were added to client request: **emails**, **phoneNumbers**

**emails** value example

``
"emails": [
{"email": some+email+1@eltexsoft.com", "isPrimary" : 1"},
{"email": some+email+2@eltexsoft.com", "isPrimary" : 0"},
]
``

Email with ``"isPrimary" : 1"`` will be saved to **account** table to **email** field



same structure for **phoneNumbers** value

``
"phoneNumbers": [
{"phoneNumber": some+email+1@eltexsoft.com", "isPrimary" : 1"},
{"phoneNumbers": some+email+2@eltexsoft.com", "isPrimary" : 0"},
]
``
phoneNumber with ``"isPrimary" : 1"`` will be saved to **account_profile** table to **phoneNumber** field


Each time when client account updates, new data about emails and phone number will be compared with same data on DB. 
In case data about email or phone exists in DB but absent in POST request - this row in DB will be deleted. 

Twilio phone number validation
---
Using `application/common/components/validators/TwilioPhoneValidator.php`.

If environment variable `DISABLE_TWILIO_PHONE_VALIDATION = 1` validator will always return **true**

Details of API request here: `\common\components\Twilio::getPhoneInfo()`
In case of validation error new row will be added to **phone_validation** table
In case of validation success row in **phone_validation** table will be related to row in **account_phones** table

