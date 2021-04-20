
Usage
-----

This behavior provides support for custom records order setup via column-based position index.

This extension provides [[\modules\account\behaviors\PositionBehavior]] ActiveRecord behavior for such solution
support in Yii2. You may attach it to your model class in the following way:

```php
class EmployeeClient extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'positionBehavior' => [
                'class' => PositionBehavior::className(),
                'positionAttribute' => 'position',
            ],
        ];
    }
}
```

Behavior uses the specific integer field of the database entity to set up position index.
Due to this the database entity, which the model refers to, must contain field [[positionAttribute]].

In order to display custom list in correct order you should sort it by [[positionAttribute]] in ascending mode:

```php
$records = EmployeeClient::find()->orderBy(['position' => SORT_ASC])->all();
foreach ($records as $record) {
    echo $record->position . ', ';
}
// outputs: 1, 2, 3, 4, 5,...
```


## Position saving <span id="position-saving"></span>

Being attached, behavior automatically fills up `positionAttribute` value for the new record, placing it to the end
of the list:

```php
echo EmployeeClient::find()->count(); // outputs: 4

$employeeClient = new EmployeeClient();
$employeeClient->save();

echo $employeeClient->position // outputs: 5
```

However, you may setup position for the new record explicitly:

```php
echo EmployeeClient::find()->count(); // outputs: 4

$employeeClient = new EmployeeClient();
$employeeClient->position = 2; // enforce position '2'
$employeeClient->save();

echo $employeeClient->position // outputs: 2 !!!
```


## Position switching <span id="position-switching"></span>

Existing record can be moved to another position using following methods:

 - [[movePrev()]] - moves record by one position towards the start of the list.
 - [[moveNext()]] - moves record by one position towards the end of the list.
 - [[moveFirst()]] - moves record to the start of the list.
 - [[moveLast()]] - moves record to the end of the list.
 - [[moveToPosition()]] - moves owner record to the specific position.

You may as well change record position through the attribute, provided to `positionAttribute` directly:

```php
$employeeClient = EmployeeClient::find()->andWhere(['position' => 3])->one();
$employeeClient->position = 5; // switch position to '5'
$employeeClient->save();
```


## Position in group <span id="position-in-group"></span>

Sometimes single database entity contains several listings, which require custom ordering, separated logically
by grouping attributes. For example: Employee client may be grouped by categories, while inside single category
questions should be ordered manually. For this case [[\modules\account\behaviors\PositionBehavior::$groupAttributes]]
can be used:

```php
class EmployeeClient extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'positionBehavior' => [
                'class' => PositionBehavior::className(),
                'positionAttribute' => 'position',
                'groupAttributes' => [
                    'employeeId' // multiple lists varying by 'employeeId'
                ],
            ],
        ];
    }
}
```

In this case behavior will use owner values of `groupAttributes` as additional condition for position
calculation and changing:

```php
echo EmployeeClient::find()->andWhere(['categoryId' => 1])->count(); // outputs: '4'
echo EmployeeClient::find()->andWhere(['categoryId' => 2])->count(); // outputs: '7'

$record = new EmployeeClient();
$record->categoryId = 1;
$record->save();
echo $record->position // outputs: '5'

$record = new EmployeeClient();
$record->categoryId = 2;
$record->save();
echo $record->position // outputs: '8'
```


## List navigation <span id="list-navigation"></span>

Records with custom position order applied make a chained list, which you may navigate if necessary.
You may use [[\modules\account\behaviors\PositionBehavior::getIsFirst()]] and [[\modules\account\behaviors\PositionBehavior::getIsLast()]]
methods to determine if particular record is the first or last one in the list. For example:

```php
echo EmployeeClient::find()->count(); // outputs: 10

$firstItem = EmployeeClient::find()->andWhere(['position' => 1])->one();
echo $firstItem->getIsFirst(); // outputs: true
echo $firstItem->getIsLast(); // outputs: false

$lastItem = EmployeeClient::find()->andWhere(['position' => 10])->one();
echo $lastItem->getIsFirst(); // outputs: false
echo $lastItem->getIsLast(); // outputs: true
```

Having a particular record instance, you can always find record, which is located at next or previous position to it,
using [[\modules\account\behaviors\PositionBehavior::getNext()]] or [[\modules\account\behaviors\PositionBehavior::getPrev()]] method.
For example:

```php
$employeeClient = EmployeeClient::find()->andWhere(['position' => 5])->one();

$nextItem = $employeeClient->findNext();
echo $nextItem->position; // outputs: 6

$prevItem = $employeeClient->findPrev();
echo $prevItem->position; // outputs: 4
```

You may as well get the first and the last records in the list. For example:

```php
echo EmployeeClient::find()->count(); // outputs: 10
$employeeClient = EmployeeClient::find()->andWhere(['position' => 5])->one();

$firstItem = $employeeClient->findFirst();
echo $firstItem->position; // outputs: 1

$lastItem = $employeeClient->findLast();
echo $lastItem->position; // outputs: 10