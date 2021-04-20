<?php
    /** @var \yii\web\View $this */
    if ($model->account->isCompanyClient()):
        $coordinates = $model->getCoordinates(true, .02, .1);
        $this->registerJsFile(
            'https://maps.googleapis.com/maps/api/js?key=' . env('GOOGLE_MAPS_API_KEY') . '&callback=initMap',
            [
                'async'=>true,
                'defer'=>true,
            ]
        );
?>
    <div id="map" class="google-map"></div>

    <script>
      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 16,
          center: {
            lat: <?= $coordinates['latitude'] ?>,
            lng: <?= $coordinates['longitude'] ?>,
          },
        });

        new google.maps.Circle({
          strokeColor: '#ff0000',
          strokeOpacity: 0.8,
          strokeWeight: 1,
          fillColor: '#ff0000',
          fillOpacity: 0.50,
          map: map,
          center: {
            lat: <?= $coordinates['latitude'] ?>,
            lng: <?= $coordinates['longitude'] ?>,
          },
          radius: <?= round($coordinates['radius'] * 1609.344, 2) ?>,
        });
      }
    </script>
<?php endif; ?>
