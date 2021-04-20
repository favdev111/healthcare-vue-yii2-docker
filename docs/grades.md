Grades
=======
API constants
------
```GET /constans/``` request contains array with list categories and related grades

Frontend
-------
There are 2 selects on front: **grade category select**, **grade select (gradeId)**

For each entity you need just to provide **gradeId** field. Now functionality connected to ClientProfile and ClientChildren entities, both in ```POST client, PUT client/{id}```` requests.

To remove grade related to client provide "gradeId:null".

In case Client Children: if grade already exists and not provided during update, it will be deleted. You can also provide "gradeId:null" to delete related grade. 

Displayed value in grade category select should be related to client's **gradeId**. 
In case of change value in **grade category select** - need to update **grade select** with data related to selected category (look for /constants/ request).  

Examples:
**PUT client/{id}**
```json
{
  "firstName": "Client",
  "lastName": "Test",
  "phoneNumbers": [
    {"isPrimary": 1, "phoneNumber": "3809830812"}
  ],
  "emails": [
   {"email": "client.test@example.com", "isPrimary": 1}
  ],
"gradeId": 10,
"childrenData": [ {"id": "5", "firstName": "xxx", "lastName": "xxx", "gradeId":10, "isDeleted": 0}]
}
```

**GET client/{id}**
```json
{
"id": 15,
"email": "client.test@example.com",
  "hourlyRate": 80,
  "subjects": [
    {
      "name": "SCIENCE",
      "text": "SCIENCE",
      "id": "15-science"
    }
  ],
  "profile": {
      "firstName": "Client",
      "lastName": "Test",
      "zipCode": "10022",
      "address": "650 Madison Ave, New York, NY",
      "phoneNumber": "3809830812",
      "mainPhoneNumberType": 2,
      "gender": null,
      "schoolName": null,
      "schoolGradeLevelId": 4,
      "schoolGradeLevel": null,
      "grade": {
        "id": 10,
        "name": "6th Grade",
        "category": "College",
        "updateGroup": 1
      },
      "startDate": "2020-12-18"
    }
}
```
**schoolGradeLevelId** and **schoolGradeLevel** are old values, deprecated. 


DB structure
----

**grades** table - contains list of grades. Model: **\modules\account\models\Grade**


**grade_items** table contains relations between some entity and grade. Model: **\modules\account\models\GradeItem** 
Type of related entity stores in **itemType** field. List of possible types described in model. Now it is: Profile (itemType = 1 - id from account_profile table) and ClientChildren (itemType = 2 - id from client_children table)

Behaviour
-------
 **\common\components\behaviors\HasGradeRelationBehavior** - describes "grade" logic.
  
 To connect "grade" functionality to new entity need to create new constant for itemType in model: **\modules\account\models\GradeItem** and connect described behaviour to model, providing new itemType.
 
 Console commands
 ---
 Controller **\console\controllers\GradeController**
 
 Command: ``php yii grade/update``
 Update grades each year. Use in CRON task each August 1st. 
 Logic: if current grade has **updateGroup** and next grade with same **updateGroup** exists - value of **gradeId** field in each not deleted GradeItem will be changed to next.
 You can provide id of GradeItem ``php yii grade/update {id}``
 
 
 Command: ``php yii grade/fill``
 Fill GradeItems using old grade values of clientChildren
 Logic: select all clientChildren, if **schoolGradeLevel** between 1 and 12 set up grade value (create GradeItem).  
 You can provide id of ClientChildren ``php yii grade/fill {id}``