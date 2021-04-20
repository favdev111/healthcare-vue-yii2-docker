(function ($) {
    var $modal = $('#guestApplyInfo');
    var $formButton = $('#guestApplyInfoForm button[type="submit"]');

    $('#jobApplyButton').on('click', function (event) {
        event.preventDefault();
        $formButton.button('reset');
        $modal.modal('show');
    });

    $('#guestApplyInfoForm').on('submit', function (event) {
        event.preventDefault();

        $formButton.button('loading');

        $.ajax({
            url: App.endpoints.job.lead,
            method: 'POST',
            data: $(this).serializeArray(),
            success : function (result) {
                var formName = 'TutorForm';
                var data = JSON.parse(localStorage.getItem('formData')) || [];

                for (var field in result) {
                    if(result.hasOwnProperty(field)) {
                        setItem(
                            data,
                            formName + '[' + field + ']',
                            result[field]
                        );
                    }
                }

                localStorage.setItem('formData', JSON.stringify(data));
                setTimeout("$(document.body).append($('#jobLeadApplyTrack').html());", 0);
                setTimeout(function () {
                    window.location = $('#jobApplyButton').attr('href');
                }, 300);
            },
            error: function (result) {
                $formButton.button('reset');
                if (result.status === 422) {
                    CommonHelper.showAllErrorsFromApi(result.responseJSON, true);
                }
            }
        });
    });

    function setItem(data, key, value) {
        for (var i = 0; i < data.length; i++) {
            if (data[i]['name'] !== key) {
                continue;
            }

            if (data[i]['value'] === '') {
                data[i]['value'] = value;
            }

            return;
        }

        data.push({
            name: key,
            value: value,
        });

        return;
    }
})(jQuery);
