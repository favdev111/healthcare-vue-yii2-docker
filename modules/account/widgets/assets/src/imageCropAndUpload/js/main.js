function initImageCropAndUpload(
    ajaxOptions,
    pluginOptions,
    callback,
    $input,
    $inputFileImageSelector,
    $modal
) {
    var $image = $('.crop-image-container > img', $modal);

    function resetInput() {
        $input.val('');
    }

    $inputFileImageSelector.on('change', function(evt) {
        var files = evt.target.files;

        if (typeof files[0] === 'undefined') {
            resetInput();
            return false;
        }

        var file = files[0];

        // Only process image files.
        if (!file.type.match('image/(png|jpeg|jpg)')) {
            resetInput();
            toastr.error('Must be an image.');
            return false;
        }

        if (file.size > (4 * 1024 * 1024)) {
            resetInput();
            toastr.error('Maximum photo size is 4MB');
            return false;
        }

        var reader = new FileReader();
        reader.onload = function(event) {
            var content = event.target.result,
                img = new Image();

            img.onload = function() {
                if (this.width < 180 || this.height < 180) {
                    toastr.error('Minimum photo dimensions are 180x180');
                    resetInput();
                    return false;
                }

                $('.crop-image-container > img', $modal).attr('src', content);
                    $modal.modal('show');
            };

            img.src = content;
        };

        // Read in the image file as a data URL.
        reader.readAsDataURL(file);
        $($inputFileImageSelector).val('')
    });

    var cropBoxData,
        canvasData;

        $modal.on("shown.bs.modal", function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal

    $image.cropper(
        $.extend({
            built: function () {
                // Strict mode: set crop box data first
                $image.cropper('setCropBoxData', cropBoxData);
                $image.cropper('setCanvasData', canvasData);
            },
            dragend: function() {
                cropBoxData = $image.cropper('getCropBoxData');
                canvasData = $image.cropper('getCanvasData');
            }
        }, pluginOptions));
    }).on('hidden.bs.modal', function () {
        cropBoxData = $image.cropper('getCropBoxData');
        canvasData = $image.cropper('getCanvasData');
        $image.cropper('destroy');
    });

    $('.crop-submit', $modal).on('click', function(e) {
        e.preventDefault();

        var content = $image.cropper('getCroppedCanvas', { minWidth: 180, minHeight: 180, width: 600, height: 600 }).toDataURL();

        if (callback) {
            callback(content);
            $modal.modal("hide");
        } else {
            useAjaxCallback(content);
        }
    });

        $modal.on('click', '[data-cropper-function]', function() {
        var command = $(this).data('cropper-function');

        switch (command) {
            case 'zoom+':
            $image.cropper('zoom', 0.1);
                break;

            case 'zoom-':
            $image.cropper('zoom', -0.1);
                break;

            case 'left':
            $image.cropper('move', -10, 0);
                break;

            case 'right':
            $image.cropper('move', 10, 0);
                break;

            case 'up':
            $image.cropper('move', 0, -10);
                break;

            case 'down':
            $image.cropper('move', 0, 10);
                break;

            case 'rotate-right':
            $image.cropper('rotate', 90);
                break;

            case 'rotate-left':
            $image.cropper('rotate', -90);
                break;

            case 'reset':
            $image.cropper('reset');
                break;
        }
    });

    function useAjaxCallback(content) {
        $.ajax($.extend({
            method: "POST",
            url: App.endpoints.tutor.setAvatar,
            data: {
                'TutorForm[avatar]': content,
            },
            error: function() {
                alert("Error while cropping");
            },
            success: function (data) {
                if (!data.success) {
                    $.each(data.error.avatar, function (ind, elem) {
                        toastr.error(elem);
                    });
                } else {
                    var url = data.url + '?time=' + (new Date()).getTime();
                    $('#profile-avatar').attr('src', url);
                    $('#profile-avatar-small').attr('src', url);
                    toastr.success('Your photo has been updated');
                }

                $modal.modal("hide");
            }
        }, ajaxOptions));
    }
}
