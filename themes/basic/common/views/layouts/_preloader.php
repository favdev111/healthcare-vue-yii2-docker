<?php

/**
 * @var $this \yii\web\View
 */

$this->registerJs(<<<JS
$(function () {
    // preloader
    setTimeout(function () {
        $('#main-preloader').addClass('remove');
        $(window).trigger('resize');
    }, 1000);
});
JS
);
$this->registerCss(
    '.main-preloader{position:fixed;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;background:#fff;left:0;top:0;right:0;bottom:0;margin:auto;z-index:9999999;width:100%;height:100vh}.main-preloader img{width:50px}.main-preloader.remove{opacity:0;pointer-events:none;z-index:-99}'
)
?>
<div class="main-preloader" id="main-preloader">
    <img src="<?= $this->theme->getUrl('img/preloader.gif') ?>" alt="HT logo">
</div>
