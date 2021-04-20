function showSubjectError(subject,e)
{
    if (!subject) {
        toastr.error('Select a subject');
        e.preventDefault();
        return false;
    }
    return true;
}

function checkZipCode(zipCode) {
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'POST',
            url: '/check-zip-code/' + zipCode + '/',
        }).success(() => {
            resolve();
        }).error(() => {
            showZipErrorToastr();
            reject();
        });
    });
}

function showZipErrorToastr() {
    toastr.error('Zip code is invalid. Please enter a valid zip code');
}

function showZipcodeError(zipCode, e) {
    var pattern = /(^\d{5}$)|(^\d{5}-\d{4}$)/;
    if (!pattern.test(zipCode)) {
        toastr.error('Zip code is invalid');
        e.preventDefault();
        return Promise.reject();
    } else {
        return checkZipCode(zipCode);
    }
}

function checkHeaderSearch(subject,zipCode,e) {
    toastr.clear();

    let isSubjectValid = showSubjectError(subject,e);

    if (!isSubjectValid) {
        return Promise.reject();
    }

    return showZipcodeError(zipCode, e);
}

$(function () {
    toastr.options = {
        "preventDuplicates": true,
        "preventOpenDuplicates": true,
        "closeButton": true,
    };

    $(document).on('click', '.btn-loading', function () {
        $(this).button('loading');
    });

    $('.phone-link, [data-analytics="phone"]').click(function () {
        App.analytics.trackGa('PhoneCall', 'PhoneClick', 'Call');
    });

    $('[data-analytics="HeaderPhoneCall"]').click(function () {
        App.analytics.trackGa('HeaderPhoneCall', 'PhoneClick', 'Call');
        if (App.googleConversions.phoneNumberCta) {
            App.analytics.trackConversion(
                App.googleConversions.phoneNumberCta.action,
                App.googleConversions.phoneNumberCta.label
            );
        }
    });

    $('[data-analytics="FooterPhoneCall"]').click(function () {
        App.analytics.trackGa('FooterPhoneCall', 'PhoneClick', 'Call');
    });

    setTimeout(function () {
        App.analytics.trackGa(
            'adjusted bounce rate' + (!App.isGuest ? ' (logged-in)' : ''),
            'page visit 45 seconds or more'
        );
    }, 45000);
});

/**
 * @param evt
 * @param length
 */
function checkLengthLimit(evt, length) {
    var theEvent = evt;
    var key = theEvent.keyCode || theEvent.which;
    if (theEvent.target.value.length >= length && key !== 8) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
}

function createUrlWithParams(url, params, nameParams, hash) {
    if (!Array.isArray(params)) {
        return url;
    }

    params.map(function (item, index) {
        url = url.replace(new RegExp("\\b" + '\/' + index.toString() + "\\b"), '/' + encodeURIComponent(item));
    });

    var nameParamsArray = [];
    for (var property in nameParams) {
        if (!nameParams.hasOwnProperty(property)) {
            continue;
        }
        nameParamsArray.push(encodeURIComponent(property) + '=' + encodeURIComponent(nameParams[property]));
    }

    if (nameParamsArray.length > 0) {
        url = url + '?' + nameParamsArray.join('&');
    }

    if (hash && hash.length > 0) {
        url = url + '#' + hash;
    }

    return url;
}
