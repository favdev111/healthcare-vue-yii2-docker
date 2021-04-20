##Settings for project:
Store different types of settings in different formats. Cached.

####Add
```
Yii::$app->settings->set('section', 'key', $data);
```
`$data` may contain any data that can be put in JSON field

####Get
```
Yii::$app->settings->get('section', 'key');
```

####Check if setting is exists
```
Yii::$app->settings->has('section', 'key');
```

####Get all keys from section
```
Yii::$app->settings->getAllBySection('section', 'default value');
```

####Remove config
```
Yii::$app->settings->remove('section', 'key');
```
