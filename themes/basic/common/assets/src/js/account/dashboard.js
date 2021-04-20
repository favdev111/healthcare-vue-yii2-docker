var bankAccountRequestSent = false;

jQuery(function(){

    $(document).on('mouseenter', '[data-show-tooltip]', function() {
        $(this).next('.help-block').toggleClass('active');
    });
    $(document).on('click', '[data-hide-tooltip]', function() {
        $('.help-block').removeClass('active');
    });

    var $formInfo = $('#payment-info-form');
    $formInfo.submit(function(event) {
        if ($('#paymentinfo-ssn').length && $('#paymentinfo-ssn').val().length < 4) {
            toastr.error('SSN must be 4 digits');
            return false;
        }
        var accountNumberLength = $('#paymentinfo-accountnumber').val().length;
        if (accountNumberLength == 0){
            toastr.error('Account number is required');
            return false;
        }
        if (accountNumberLength > 17) {
            toastr.error('Account number must be no more than 17 digits');
            return false;
        }
        if ($('#paymentinfo-routingnubmer').val().length != 9) {
            toastr.error('Routing number must be 9 digits');
            return false;
        }
        if ($('#paymentinfo-bankname').val().length == 0) {
            toastr.error('Bank name is required');
            return false;
        }
        // Disable the submit button to prevent repeated clicks:
        //$form.find('.submit').prop('disabled', true);
        $formInfo.find('.submit').button('loading');

        // Request a token from Stripe:


        if (!bankAccountRequestSent) {
            bankAccountRequestSent = true;
            Stripe.bankAccount.createToken($formInfo, stripeResponseBankHandler);
        }


        // Prevent the form from being submitted:
        return false;
    });

    $('.payment-card input').on('change', function(e) {
        e.stopPropagation();
        if ($(this).is(':checked')) {
            $('.payment-card').removeClass('active');
            $(this).parents('.payment-card').addClass('active');
        } else {
            $(this).parents('.payment-card').removeClass('active');
        }
        var id = $(this).val();

        $('.content-payment-tutor').find("[data-radio='"+id+"']").each(function(){
            $(this).prop('checked', true)
        });
        $.ajax({
            method: "GET",
            url: createUrlWithParams(App.endpoints.tutor.activeBankAccount, [id]),
            success: function(ans) {
                toastr.success(ans.message);
            }
        });
    });

    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if ((target == '#tabPaymentInfoSecondStep')) {

            if (!$('#profile-address').val().length) {
                toastr.error('Address Line 1 is required');
                return false;
            }

            if (!$('#profile-zipcode').val().length) {
                toastr.error('ZIP Code / Postal Code is required');
                return false;
            }

            if ($('#paymentinfo-ssn').val().length < 4) {
                toastr.error('SSN must be 4 digits');
                return false;
            }
        }
    });

    $('#paymentinfo-zip').keypress(function(evt) {
        checkNumber(evt);
        checkLengthLimit(evt, 5);
    });
    $('#paymentinfo-routingnubmer').keypress(function(evt) {
        checkNumber(evt);
        checkLengthLimit(evt, 9);
    });
    $('#paymentinfo-accountnumber').keypress(function(evt) {
        checkNumber(evt);
        checkLengthLimit(evt, 17);
    });

    $('#paymentinfo-personal_id_number').keypress(function(evt) {
        checkNumber(evt);
        checkLengthLimit(evt, 9);
    });

    $('#paymentinfo-ssn').on('keypress', function (evt) {
        checkNumber(evt);
        checkLengthLimit(evt, 4);
    });
    $('#inputZipCode').keypress(function(evt) {
        checkNumber(evt);
        checkLengthLimit(evt, 5);
    });

    var $formExtra = $('#payment-extra-info');
    $formExtra.submit(function(event) {
        // Disable the submit button to prevent repeated clicks:
        $formExtra.find('.submit').button('loading');

        // Request a token from Stripe:
        Stripe.piiData.createToken($formExtra, stripeResponsePersonalHandler);

        // Prevent the form from being submitted:
        return false;
    });

    var education = 500;

    // Toaster settings
    toastr.options.closeButton = true;
    toastr.options.preventDuplicates = true;
    toastr.options.timeOut = 5000; // How long the toast will display without user interaction
    toastr.options.extendedTimeOut = 2000; // How long the toast will display after a user hovers over it

    $('#btnShowEditPanel').on('click', function() {
        $('.hr-block.edit-block').toggleClass('active');
        $('#profile-hourly-rate').trigger('keyup');
    });

    $('#btnShowEditPanel').on('click', function (e) {
        if (!$('.hr-block.edit-block').hasClass('active')) {
            document.getElementById('profile-rate').style.display = 'block';
            var button = $(this);
            $(this).text('Change hourly rate');
            var rate = $('#profile-hourly-rate').val();
            $('#profile-rate').text('$ ' + rate);
            $.ajax({
                url: App.endpoints.tutor.setHourlyRate,
                data: {rate: rate},
                type: 'POST',
                success: function (data) {
                    if (!data.success) {
                        button.text('Set new hourly rate');
                        $('.hr-block.edit-block').toggleClass('active');
                        $('#profile-hourly-rate').trigger('keyup');
                        $.each(data.error.hourlyRate, function (ind, elem) {
                            toastr.error(elem);
                        });
                    } else {
                        toastr.success('Hourly rate has been updated');
                    }
                }
            });
        } else {
            document.getElementById('profile-rate').style.display = 'none';
            $(this).text('Set new hourly rate');
        }
    });

    $('#profile-hourly-rate').on('keyup', function(e) {
        $(this).val($(this).val().replace (/\D/, ''));
        $('#profile-rate').text('$' + parseInt($(this).val()));
    });

    $('#edit-profile-form-education').on('submit', function(e) {
        var form = $('#edit-profile-form-education');
        var data = $(form).serialize();
        if (App.global.isMobile) {
            if (data.indexOf('AddEducation') == -1 && data.indexOf('RemoveEducation') == -1) {
                return;
            }
        }
        e.preventDefault();
        $.ajax({
            url: App.endpoints.tutor.editProfile,
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
});

function stripeResponsePersonalHandler(status, response) {
    // Grab the form:
    var $form = $('#payment-extra-info');

    if (response.error) { // Problem!

        bankAccountRequestSent = false;
        // Show the errors on the form:
        toastr.error(response.error.message);
        $form.find('.submit').button('reset');

    } else { // Token created!

        // Get the token ID:
        var token = response.id;

        // Insert the token into the form so it gets submitted to the server:
        $form.append($('<input type="hidden" name="piiToken" />').val(token));

        // Submit the form:
        $form.get(0).submit();

    }
}

function stripeResponseBankHandler(status, response) {
    // Grab the form:
    var $form = $('#payment-info-form');
    if (response.error) { // Problem!
        bankAccountRequestSent = false;
        // Show the errors on the form:
        toastr.error(response.error.message);
        $form.find('.submit').button('reset');

    } else { // Token created!

        // Get the token ID:
        var token = response.id;

        // Insert the token into the form so it gets submitted to the server:
        $form.append($('<input type="hidden" name="bankToken" />').val(token));

        // Submit the form:
        $form.get(0).submit();

    }
}
