(function () {
    let confirm = false;

    function checkZipCodeSuccessCallback(ans) {
        let zipCode = null;
        if (
            ans.types.indexOf('street_address') !== -1
            || ans.types.indexOf('premise') !== -1
            || ans.types.indexOf('subpremise') !== -1
        ) {
            $.each(ans.address_components, function (ind, el) {
                if (el.types[0] === 'postal_code' && el.long_name) {
                    zipCode = el.long_name;
                }
            });
        }
        if (zipCode) {
            $('#profile-placeId').val(ans.place_id);
            $('#profile-zipcode').val(zipCode);
            confirm = true;
            return true;
        }

        toastr.error('Address is invalid. Please enter a valid location.');
        $('#profile-zipcode').val('');
        return false;
    }

    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        checkZipCodeSuccessCallback(place);
    });

    $('#profile-address')
        .on('change', function() {
            if (!confirm) {
                $('#profile-zipcode').val('');
                $('#profile-placeId').val('');
            }
        })
        .on('focus', function() {
            confirm = false;
        });

    $('#login-form, #about-me-form').on('submit', function () {
       $('#hiddenZip').val($('#profile-zipcode').val());
    });
})();
