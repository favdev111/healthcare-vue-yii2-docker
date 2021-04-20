jQuery(function(){

    var education = 500;
    $('#inputZipCode').keypress(function(evt) {
        checkNumber(evt);
        var theEvent = evt || window.event;
        var key = theEvent.keyCode || theEvent.which;
        if(this.value.length >= 5 && key != 8) {
            return false;
        }
    });

    // Toaster settings
    toastr.options.closeButton = true;
    toastr.options.preventDuplicates = true;
    toastr.options.timeOut = 5000; // How long the toast will display without user interaction
    toastr.options.extendedTimeOut = 2000; // How long the toast will display after a user hovers over it
    $('#profileInputFile').on('change', function(evt) {

        var files = evt.target.files,
            f,
            avatarFile;

        // $('.media-object').css('backgroundImage', 'url(' + $('.file-control--on-image').data('default') + ')');

        if (typeof files[0] === 'undefined') {
            return false;
        }

        f = files[0];

        // Only process image files.
        if (!f.type.match('image/(png|jpeg|jpg)')) {
            toastr.error('Must be an image.');
            return false;
        }

        if (f.size > (4 * 1024 * 1024)) {
            toastr.error('Maximum photo size is 4MB');
            return false;
        }

        var reader = new FileReader();

        // Closure to capture the file information.
        reader.onload = function(event) {
            var content = event.target.result,
                img = new Image();

            img.onload = function() {
                if (this.width < 180 || this.height < 180) {
                    toastr.error('Minimum photo dimensions are 180x180');
                    return false;
                }
                //$('.uload-image-container').addClass('uploaded');
                //$('.media-object').css('backgroundImage', 'url(' + content + ')');
                avatarFile = content;
                $('#croppie-profile').html('');
                const $uploadCrop = $('#croppie-profile').croppie({
                    url: content,
                    showZoomer: false,
                    enableExif: true,
                    viewport: {
                        width: 180,
                        height: 180,
                        type: 'circle'
                    },
                    boundary: {
                        width: 180,
                        height: 180
                    }
                });
                var accessSend = true;
                toastr.options.timeOut = 1000;
                $uploadCrop.on('update', function (ev, data) {
                    $('#croppie-profile').croppie('result', {type: 'canvas', size : {width: 600, height: 600}}).then(function(img) {

                        if (accessSend) {
                            $.ajax({
                                url: '/backend/accounts/tutor/set-avatar/?id='+tutorId,
                                data: {
                                    'TutorForm[avatar]': img
                                },
                                type: 'POST',
                                beforeSend: function () {
                                    accessSend = false;
                                },
                                success: function (data) {
                                    if (!data.success) {
                                        $.each(data.error.avatar, function (ind, elem) {
                                            toastr.error(elem);
                                        });
                                    } else {
                                        //$('#profile-avatar').attr('src', data.url);
                                        $('#profile-avatar-small').attr('src', data.url);
                                        toastr.success('Your photo has been updated');
                                    }
                                    accessSend = true;
                                }
                            });
                        }
                    });
                });
            };

            img.src = content;
        };

        // Read in the image file as a data URL.
        reader.readAsDataURL(f);

    });
    $(document).on('click touchstart', '.profile-remove-education', function (e) {
        e.preventDefault();
        if ($('.profile-education-block').length > 1) {
            var id = $(this).data('id');
            $(this).closest('.profile-education-block').fadeOut('fast', 'linear', function () {
                $(this).remove();
                if (id != 0) {
                    $('#edit-profile-form-education').append('<input type="hidden" name="RemoveEducation[]" value="' + id + '">');
                }
            });
        } else {
            toastr.error('At least one form of education is required');
        }
    });
    $(document).on('click', '#profile-add-education', function (e) {
        e.preventDefault();
        var block = $(this).closest('#profile-education-wrapper').find('.profile-education-block')[0];
        block = $(block).clone();
        var newCount = education + 1;
        var oldId = block.find('.profile-remove-education').data('id');

        block.find('.profile-remove-education').data('id', 0);
        block.find('[name]:input').each(function() {
            var name = $(this).attr('name');
            var newName = name.replace('educations[' + oldId+ ']', 'AddEducation[' + newCount + ']');
            $(this).attr('name', newName);
            var id = $(this).attr('id');
            var newId = id.replace(oldId, newCount);
            $(this).attr('id', newId);
            $(this).val('').removeProp('selected').removeProp('checked');
        });

        /** select2 activate **/
        block.find('.select2').remove();
        $('.profile-education-wrapper').append(block);
        var par1 = block.find('.select2-hidden-accessible').data('s2-options');
        var par2 = block.find('.select2-hidden-accessible').data('krajee-select2');
        jQuery.when(jQuery('#educations-' + newCount + '-collegeid').select2(window[par2])).done(initS2Loading('educations-'+newCount+'-collegeid',par1));
        /** select2 activate **/

        education++;
    });

    $(document.body).on('change', '[data-degree]', function() {
        var $el = $(this),
            $wrapper = $el.parents('.well--container');

        $wrapper.find('[data-education-type]').addClass('hidden');

        if ($el.val() == 5) {
            $wrapper.find('[data-education-type="enrolled"]').removeClass('hidden');
        } else if ($el.val() != '') {
            $wrapper.find('[data-education-type="graduated"]').removeClass('hidden');
        }
    });

    $('[data-degree]').trigger('change');

    $('#edit-profile-form-education').on('submit', function(e) {
        var form = $('#edit-profile-form-education');
        var data = $(form).serialize();
        if (data.indexOf('AddEducation') == -1 && data.indexOf('RemoveEducation') == -1) {
            return;
        }
        e.preventDefault();
        $.ajax({
            url: '/backend/accounts/tutor/edit-profile/?id='+tutorId,
            method: 'post',
            data: data,
            success: function(ans) {
                if (ans.length > 0) {
                    var errorMessage = '';
                    $.each(ans, function (i, val) {
                        $.each(val, function (k, mes) {
                            errorMessage += mes[0]+'<br>';
                        });
                    });
                    toastr.error(errorMessage);
                    return false;
                }
                $(form).off('submit').submit();
            }
        });
    });
});
function checkNumber(evt) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    if (key == 37 || key == 38 || key == 39 || key == 40 || key == 8 || key == 46) { // Left / Up / Right / Down Arrow, Backspace, Delete keys
        theEvent.returnValue = false;
        return false;
    }
    key = String.fromCharCode(key);
    var regex = /[0-9]|\./;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
}




















