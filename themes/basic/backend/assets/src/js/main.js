"use strict";

let app = {
    init: function () {
        this.pjax.loader();
        this.events.activeFormAjax();
    },
    pjax: {
        loader: function () {
            const LOADER_ID = 'loader';
            let loaderSelector = ' #' + LOADER_ID;
            let spinner = '<div id="{loaderID}"><div class="spinner-border text-primary" role="status">\n' +
                '<span class="sr-only">Loading...</span>\n' +
                '</div></div>';
            spinner = spinner.replace('{loaderID}', LOADER_ID);

            $(document).on('pjax:send', function (xhr, textStatus, options) {
                let pjaxSelector = '#' + xhr.target.id;
                let pjaxLoader = pjaxSelector + loaderSelector;
                if ($(document).is(pjaxLoader)) {
                    $(pjaxLoader).show();
                } else {
                    $(pjaxSelector).prepend(spinner);
                }
            });

            $(document).on('pjax:complete', function (xhr, textStatus, options) {
                let pjaxSelector = '#' + xhr.target.id;
                let pjaxLoader = pjaxSelector + loaderSelector;
                if ($(document).is(pjaxLoader)) {
                    $(pjaxLoader).hide();
                }
            });
        }
    },
    events: {
        activeFormAjax() {
            $.ajaxSetup({contentType: false});          // global set for ajax, need for correctly detect contentType in ajaxBeforeSend event

            $(document).on('ajaxBeforeSend', function (event, jqXHR, settings) {
                let $form = $(event.target);
                let data = $form.data('yiiActiveForm');
                let formData = new FormData($form[0]);
                let $button = data.submitObject;

                jqXHR.setRequestHeader("X-Form-Validate", 'true');

                formData.append(data.settings.ajaxParam, $form.attr('id'));

                if ($button && $button.length && $button.attr('name')) {
                    formData.append($button.attr('name'), $button.attr('value'));
                }

                settings.data = formData;
                settings.processData = false;
                settings.dataType = false;
            });
        },
    }
};

$(document).ready(function () {
    app.init();
});
