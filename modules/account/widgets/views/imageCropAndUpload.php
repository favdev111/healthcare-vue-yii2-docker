<div class="modal fade" id="<?= $widget->id ?>Modal" role="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content--cropper">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Select crop area and click "Crop" button</h4>
            </div>
            <div class="modal-body">
                <div class="crop-image-container">
                    <?= \yii\helpers\Html::img('', $widget->imageOptions) ?>
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="zoom+"><i class="fa fa-search-plus" aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="zoom-"><i class="fa fa-search-minus" aria-hidden="true"></i></button>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="left"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="right"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="up"><i class="fa fa fa-arrow-up" aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="down"><i class="fa fa-arrow-down" aria-hidden="true"></i></button>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="rotate-right"><i class="fa fa-repeat" aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="rotate-left"><i class="fa fa-undo" aria-hidden="true"></i></button>
                </div>

                <div class="btn-group ml30">
                    <button type="button" class="btn btn-primary btn-cropper" data-cropper-function="reset"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                    <button type="button" class="btn btn-default btn-cropper" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-cropper crop-submit">Crop</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        initImageCropAndUpload(
            JSON.parse('<?= $ajaxOptions ?>'),
            JSON.parse('<?= $pluginOptions ?>'),
            <?= $callback ?: 'function() {}' ?>,
            $('#<?= $widget->id ?>File'),
            $('<?= $inputFileImageSelector ?>'),
            $('#<?= $widget->id ?>Modal')
        );
    });
</script>
