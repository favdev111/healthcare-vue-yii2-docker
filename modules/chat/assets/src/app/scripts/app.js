/*
 * Q-municate chat application
 *
 * Main Module
 *
 */
define([
    'jquery', 'UserModule',
    'SessionModule', 'SettingsModule',
    'ContactModule', 'DialogModule',
    'MessageModule', 'AttachModule',
    'ContactListModule',
    'CursorModule', 'SyncTabsModule',
    'UserView',
    'DialogView', 'MessageView',
    'AttachView', 'ContactListView',
    'Events',
    'Helpers', 'QBApiCalls'
], function(
    $, User,
    Session, Settings,
    Contact, Dialog,
    Message, Attach,
    ContactList,
    Cursor, SyncTabs,
    UserView,
    DialogView, MessageView,
    AttachView, ContactListView,
    Events,
    Helpers, QBApiCalls
) {

    function QM() {
        this.models = {
            User          : new User(this),
            Session       : new Session(this),
            Settings      : new Settings(this),
            Contact       : new Contact(this),
            Dialog        : new Dialog(this),
            Message       : new Message(this),
            Attach        : new Attach(this),
            ContactList   : new ContactList(this),
            Cursor        : new Cursor(this),
            SyncTabs      : new SyncTabs(this)
        };

        this.views = {
            User          : new UserView(this),
            Dialog        : new DialogView(this),
            Message       : new MessageView(this),
            Attach        : new AttachView(this),
            ContactList   : new ContactListView(this)
        };

        this.events       = new Events(this);
        this.service      = new QBApiCalls(this);
    }

    QM.prototype = {
        init: function() {
            $.ajax({
                url: App.endpoints.blockedUsers,
                success: function (users) {
                    for (var i = 0; i < users.length; i++) {
                        if (window.usersBlocked.indexOf(parseInt(users[i])) === -1) {
                            window.usersBlocked.push(parseInt(users[i]));
                        }
                    }
                }
            });
            $.ajax({
                url: App.endpoints.unverifiedStudents,
                success: function (users) {
                    for (var i = 0; i < users.length; i++) {
                        if (window.unverifiedStudents.indexOf(parseInt(users[i])) === -1) {
                            window.unverifiedStudents.push(parseInt(users[i]));
                        }
                    }
                }
            });

            var token;

            // this.preloader();

            // QB SDK initialization
            // Checking if autologin was chosen
            if (localStorage['QM.session'] && localStorage['QM.user'] &&
                // new QB release account (13.02.2015)
                localStorage['QM.isReleaseQBAccount']) {

                token = JSON.parse(localStorage['QM.session']).token;
                this.service.init(token);

            } else if (localStorage['QM.isReleaseQBAccount']) {
                this.service.init();
            } else {
                // removing the old cached data from LocalStorage
                localStorage.clear();
                localStorage.setItem('QM.isReleaseQBAccount', '1');
                this.service.init();
            }

            this.events.init();

            Helpers.log('App init', this);
        },

        preloader: function() {
            var spinner = $('#main-preloader');

            spinner.addClass('remove');
            $('.l-wrapper section').removeClass('is-hidden');
        },
    };

    return QM;
});
