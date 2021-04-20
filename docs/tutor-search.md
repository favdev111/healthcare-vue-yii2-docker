Tutor search
=======

Elastic Search
---
For searching in ElasticSearch use class \modules\account\models\TutorSearch
Create new instance of this class, set up all search params as instance properties and use search() method.

Custom data provider
----
Now search() method of class \modules\account\models\TutorSearch using custom class for Data Provider:\common\components\ElasticActiveDataProvider. 

It has public static property $returnedClass (string). It makes possible to set up class for models which search() method will return.   
For example: if $returnedClass is equal '\modules\account\models\api\Tutor', method  search() in \modules\account\models\TutorSearch will return
Data Provider with models of class '\modules\account\models\api\Tutor'.

