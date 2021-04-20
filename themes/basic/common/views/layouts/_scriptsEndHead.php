<?php
if (
    defined('YII_ENV')
    && (YII_ENV === 'prod')
): ?>
    <?php if (isset($this->params['gaData'])) : ?>
        <script>
            if (!window.dataLayer) {
                window.dataLayer = [];
            }
            window.dataLayer.push({
                event: 'googleAnalytics',
                'pageview',
                encodeURIComponent('<?= rawurlencode($this->params['gaData']['zipCode'] . ' - ' . $this->params['gaData']['subject']) ?>'),
            });
        </script>
    <?php endif; ?>
<?php endif; ?>
<?php if (!empty($this->params['recordJaco']) && $jacoApiKey = env('JACO_API_KEY')) : ?>
    <?php // Adding Jaco Recording code using Key from env ?>
    <script type="text/javascript">
        (function(e,r){function n(e,r){e[r]=function(){e.push([r].concat(Array.prototype.slice.call(arguments,0)))}}function o(){var e=r.location.hostname.match(/[a-z0-9][a-z0-9\-]+\.[a-z\.]{2,6}$/i),n=e?e[0]:null,o="; domain=."+n+"; path=/; expires=" + new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toUTCString();r.cookie=r.referrer&&-1===r.referrer.indexOf(n)?"jaco_referer="+r.referrer+o:"jaco_referer="+t+o}var a="JacoRecorder",t="none";!function(e,r,t,i){if(!t.__VERSION){e[a]=t;for(var c=["init","identify","startRecording","stopRecording","removeUserTracking","setUserInfo","trackEvent"],s=0;s<c.length;s++)n(t,c[s]);o(),t.__VERSION=2.1,t.__INIT_TIME=1*new Date;var f=r.createElement("script");f.async=!0,f.setAttribute("crossorigin","anonymous"),f.src=i;var d=r.getElementsByTagName("head")[0];d.appendChild(f)}}(e,r,e[a]||[],"https://recorder-assets.getjaco.com/recorder_v2.js")}).call(window,window,document);
        window.JacoRecorder.init("<?= $jacoApiKey ?>");
        window.JacoRecorder.removeUserTracking();
        window.JacoRecorder.init("<?= $jacoApiKey ?>");
        <?php if (!Yii::$app->user->isGuest) : ?>
            <?php // Adding ID on Jaco side to current user ID ?>
            window.JacoRecorder.identify('#<?= Yii::$app->user->id . ' ' . Yii::$app->user->identity->email ?>', function callback(err){});
        <?php endif; ?>
    </script>
<?php endif; ?>
