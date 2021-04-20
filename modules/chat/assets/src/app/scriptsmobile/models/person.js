/*
 * Q-municate chat application
 *
 * Person Model
 *
 */
define([
    'jquery',
    'underscore',
    'quickblox',
    'backbone',
    'config'
], function(
    $,
    _,
    QB,
    Backbone,
    QMCONFIG
) {

    var App;

    var Person = Backbone.Model.extend({
        defaults: {
            full_name: null,
            email: null,
            password: '',
            phone: '',
            twitter_digits_id: null,
            avatar: null,
            avatar_url: QMCONFIG.defAvatar.url,
            status: '',
            user_tags: null
        },

        parse: function(data, options) {
            if (typeof options === 'object') {
                App = options.app;
            }

            _.each(data, function(val, key) {
                var isHasKey = _.has(this.defaults, key);
                if (key !== 'id' && !isHasKey) {
                    delete data[key];
                } else if (typeof val === 'string') {
                    data[key] = val.trim();
                }
            }, this);

            return data;
        },

        initialize: function() {

        },

    });

    return Person;

});
