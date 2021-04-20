/*global require*/
'use strict';

requirejs.config({
    // googlemaps: {
    //     params: {
    //         key: 'AIzaSyAhduIkJbVdtRm0Hz6XpkihGt8h_R8cZds',
    //         libraries: 'geometry'
    //     }
    // },
    baseUrl: 'scripts',
    shim: {
        // gmaps: {
        //     deps: ['googlemaps'],
        //     exports: "GMaps"
        // },
        handlebars: {
            exports: 'Handlebars'
        },
        cryptojs: {
            exports: 'CryptoJS'
        },
        progressbar: {
            exports: 'ProgressBar'
        },
        minEmoji: {
            exports: 'minEmoji'
        }
    },
    paths: {
        // libs
        //googlemaps: '../bower_components/googlemaps-amd/src/googlemaps',
        toastr: '../bower_components/toastr/toastr',
        //gmaps: 'https://rawgit.com/HPNeo/gmaps/master/gmaps',
        cryptojs: '../bower_components/crypto-js-lib/rollups/aes',
        jquery: '../bower_components/jquery/dist/jquery',
        moment: '../bower_components/moment/min/moment.min',
        underscore: '../bower_components/underscore/underscore',
        backbone: '../bower_components/backbone/backbone',
        handlebars: '../bower_components/handlebars/handlebars',
        quickblox: '//cdnjs.cloudflare.com/ajax/libs/quickblox/2.8.1/quickblox.min',
        progressbar: '../bower_components/progressbar.js/lib/control/progressbar',
        loadImage: '../bower_components/blueimp-load-image/js/load-image',
        canvasToBlob: '../bower_components/blueimp-canvas-to-blob/js/canvas-to-blob',
        mCustomScrollbar: '../bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar',
        nicescroll: '../bower_components/jquery.nicescroll/jquery.nicescroll.min',
        mousewheel: '../bower_components/jquery-mousewheel/jquery.mousewheel',
        timeago: '../bower_components/jquery-timeago/jquery.timeago',
        minEmoji: '../vendor/emoji/js/minEmoji',

        // Q-municate application
        config: '../configs/main_config',
        MainModule: 'app',
        // models
        UserModule: 'models/user',
        SessionModule: 'models/session',
        SettingsModule: 'models/settings',
        ContactModule: '../common/models/contact',
        DialogModule: 'models/dialog',
        MessageModule: '../common/models/message',
        AttachModule: 'models/attach',
        ContactListModule: 'models/contact_list',
        CursorModule: 'models/custom_cursor',
        SyncTabsModule: 'models/sync_tabs',
        // views
        UserView: '../common/views/user',
        DialogView: 'views/dialog',
        MessageView: 'views/message',
        AttachView: 'views/attach',
        ContactListView: '../common/views/contact_list',
        LocationView: '../common/views/location',
        // apiCalls
        QBApiCalls: 'qbApiCalls',
        // events
        Events: 'events',
        // helpers
        Helpers: '../common/helpers',
        // templates
        QMHtml: 'qmhtml'
    }
});

requirejs([
    'jquery',
    'config',
    'minEmoji',
    'MainModule',
    'Helpers'
], function(
    $,
    QMCONFIG,
    minEmoji,
    QM,
    Helpers
) {
    var APP;

    // Application initialization
    $(function() {
        $.ajaxSetup({
            cache: true
        });

        /* Materialize sdk
         *
         * Not included in requirejs dependencies as required hammer.js,
         * which often creates problems when loading
         */
        // $.getScript('https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.6/js/materialize.min.js', function() {
        //     Helpers.log('Materialize connected');
        // });

        // emoji smiles run
        // $('.smiles-group').each(function() {
        //     var obj = $(this);
        //     obj.html(minEmoji(obj.text(), true));
        // });

        APP = new QM();
        APP.init();
    });

});
