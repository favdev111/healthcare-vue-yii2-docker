/*
 * Q-municate chat application
 *
 * User Module
 *
 */
define([
    'jquery',
    'config',
    'quickblox',
    'Helpers'
], function(
    $,
    QMCONFIG,
    QB,
    Helpers
) {

    var that;

    function User(app) {
        this.app = app;
        this._remember = true;
        that = this;
    }

    User.prototype = {
        login: function() {
            var QBApiCalls = this.app.service,
                UserView = this.app.views.User,
                DialogView = this.app.views.Dialog,
                Contact = this.app.models.Contact,
                self = this,
                params;

            UserView.createSpinner();

            params = {
                login: window.App.chat.user.login,
                password: window.App.chat.user.password
            };

            QBApiCalls.createSession(params, function(session) {
                QBApiCalls.getUser(session.user_id, function(user) {
                    self.contact = Contact.create(user);

                    Helpers.log('User', self);

                    QBApiCalls.connectChat(self.contact.user_jid, function(roster) {
                        self.rememberMe();
                        UserView.successFormCallback();
                        DialogView.prepareDownloading(roster);
                        DialogView.downloadDialogs(roster);
                    });
                });
            }, true);
        },

        rememberMe: function() {
            var storage = {},
                self = this;

            Object.keys(self.contact).forEach(function(prop) {
                if (prop !== 'app') {
                    storage[prop] = self.contact[prop];
                }
            });

            localStorage.setItem('QM.user', JSON.stringify(storage));
        },

        autologin: function() {
            var QBApiCalls = this.app.service,
                UserView = this.app.views.User,
                DialogView = this.app.views.Dialog,
                Contact = this.app.models.Contact,
                storage = JSON.parse(localStorage['QM.user']),
                self = this;

            UserView.createSpinner();

            QBApiCalls.getUser(storage.id, function(user) {
                self.contact = Contact.create(user);

                Helpers.log('User', self);

                QBApiCalls.connectChat(self.contact.user_jid, function(roster) {
                    self.rememberMe();
                    UserView.successFormCallback();
                    DialogView.prepareDownloading(roster);
                    DialogView.downloadDialogs(roster);
                });
            });
        },
    };

    return User;

});
