<?php
$script = <<< JS
      function initAutocomplete() {
          autocomplete = new google.maps.places.Autocomplete(
            (document.getElementById('profile-address')),
            {types: ['geocode']});
      }
JS;
$this->registerJs($script, yii\web\View::POS_HEAD);
\frontend\assets\GoogleAutocompleteAsset::register($this);
\common\assets\ProfileAddressAutoComplete::register($this);
if (!empty($profile)) {
    echo \yii\helpers\Html::activeHiddenInput($profile, 'placeId', ['id' => 'profile-placeId']);
} else {
     echo "<input type=\"hidden\" id=\"profile-placeId\" name=\"placeId\">";
 }

