<?php
/**
 * @var array $pathList
 * @var string $selectedFile
 * @var string $fileContent
 * @var View $this
 */
use common\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;
$this->registerCss(
<<<CSS
.top-content {
display: flex;
margin: 0 60px;
justify-content: space-between;
}
#download-link{
color: white;
display: block;
}
span.red {
    color:red;
}
span.blue {
    color:blue;
}
span.orange {
    color:orange;
}
#file-content {
border-radius: 5px;
padding: 15px;
background-color: #fff;
height: 600px;
scroll-behavior: auto;
text-overflow: ellipsis;
overflow: scroll;
width: 90%;
margin: 25px auto 0 auto;
display: flex;
flex-direction: column;
padding-bottom: 0 !important;
}
CSS
);
$this->registerJs(
<<<JS
$('body').on('change', '#logFileSelect', function(ev) {
  $('#logFileForm').trigger('submit');
})
JS
);

Pjax::begin([
    'timeout' => 10000,
    'formSelector' => '#logFileForm'
]);
echo Html::beginForm(['log/index'], 'get', ['id' => 'logFileForm']);

?>
<div class="main-content">
    <div class="top-content">
        <div>
            <?= Html::dropDownList('logFile', $selectedFile, $pathList, ['id' => 'logFileSelect']);?>
        </div>
        <div class="btn btn-success">
            <a id="download-link" data-pjax="0" href="<?=Yii::$app->urlManager->createUrl(['/log/download', 'logFile' => $selectedFile])?>">Download</a>
        </div>

    </div>
    <div class="div">
        <div id="file-content">
            <?php echo $fileContent;?>
        </div>
    </div>
</div>

<?php
echo Html::endForm();
Pjax::end();
