var CommonHelper = {

    showShareSalePixel: function(data) {
        if (data.shareasaleLeadPixel) {
            $('body').append(data.shareasaleLeadPixel);
        }
    },

    showAllErrors: function(data) {
        if (!data) {
            return;
        }
        if (data.errors) {
            $.each(data.errors, function (key, value) {
                toastr.error(typeof value === 'string' ? value : value[0]);
            });
        }
        if (data.warnings) {
            $.each(data.warnings, function (key, value) {
                toastr.warning(typeof value === 'string' ? value : value[0]);
            });
        }
        if (data.message) {
            toastr.error(data.message);
        }
    },

    showAllErrorsFromApi: function(data, onlyFirstError) {
        if (!data) {
            return;
        }

        if (onlyFirstError === true) {
            toastr.error(data[0]['message']);
            return;
        }

        $.each(data, function (key, value) {
            toastr.error(value['message']);
        });
    }
};
