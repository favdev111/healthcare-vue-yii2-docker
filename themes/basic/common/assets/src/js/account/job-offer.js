jQuery(function() {
    $('body')
        .on('change','.job-counter-offer-amount', function (e) {
            var val = $(this).val();
            if (val && ((val < App.global.minOfferValue) || (val > App.global.maxOfferValue))) {
                toastr.error('Your rate should be anywhere between $'+App.global.minOfferValue+'-$'+ App.global.maxOfferValue +' per hour.');
            }
            if ($(this).val() > 0) {
                showOfferSubmit(this);
            } else {
                hideOfferSubmit(this);
            }
        })
        .on('focus','.job-counter-offer-amount', function (e) {
            showOfferSubmit(this);
        })
        .on('blur','.job-counter-offer-amount', function (e) {
            $(this).trigger('change');
        })
        .on('keypress','.job-counter-offer-amount', function (evt) {
            checkNumber(evt);
        });
        $('.job-counter-offer-amount').trigger('change');
});

function showOfferSubmit(input) {
    $(input).parents('.job-offer-wrapper').find('.job-offer-submit').removeClass('hidden');
    $(input).parents('.job-offer-wrapper').find('.job-offer-accept').addClass('hidden');
}
function hideOfferSubmit(input) {
    $(input).parents('.job-offer-wrapper').find('.job-offer-submit').addClass('hidden');
    $(input).parents('.job-offer-wrapper').find('.job-offer-accept').removeClass('hidden');
    setTimeout(function () {
        $(input).parents('.has-error').removeClass('has-error');
    }, 500);
}
