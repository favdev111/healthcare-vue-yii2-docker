/*
 * Q-municate chat application
 *
 * User View Module
 *
 */
define([
    'jquery',
    'config',
    'quickblox',
    'Helpers',
    'QMHtml',
    //'LocationView'
], function(
    $,
    QMCONFIG,
    QB,
    Helpers,
    QMHtml
    //Location
) {

    var User, ContactList, Contact;

    function UserView(app) {
        this.app = app;
        User = this.app.models.User;
        Contact = this.app.models.Contact;
        ContactList = this.app.models.ContactList;
    }

    UserView.prototype = {
        autologin: function () {
            User.autologin();
        },

        createSpinner: function () {
            $('section:visible form').addClass('is-hidden').next('.l-spinner').removeClass('is-hidden');
        },

        removeSpinner: function () {
            $('section:visible form').removeClass('is-hidden').next('.l-spinner').addClass('is-hidden');
        },

        successFormCallback: function () {
            this.removeSpinner();
            // $('#profile').find('.avatar').addClass('profileUserAvatar').css('background-image', "url(" + User.contact.avatar_url + ")");
            // $('#profile').find('.avatar').attr('data-id', User.contact.id);
            switchPage($('#mainPage'));
        },

        successSendEmailCallback: function () {
            var alert = '<div class="j-success_callback note l-form l-flexbox l-flexbox_column">';
            alert += '<span class="text text_alert text_alert_success">Success!</span>';
            alert += '<span class="text">Please check your email and click a link in the letter in order to reset your password</span>';
            alert += '</div>';

            this.removeSpinner();
            $('section:visible form').addClass('is-hidden').after(alert);
        },

        contactPopover: function (objDom) {
            var ids = objDom.parent().data('id'),
                dialog_id = objDom.parent().data('dialog'),
                roster = ContactList.roster,
                dialogs = ContactList.dialogs,
                htmlTpl;

            htmlTpl = QMHtml.User.contactPopover({
                'dialogId': dialog_id,
                'dialogType': dialogs[dialog_id].type,
                'occupantsIds': dialogs[dialog_id].occupants_ids,
                'ids': ids
            }, roster[ids]);

            objDom.after(htmlTpl)
                .parent().addClass('is-contextmenu');

            appearAnimation();

            var elemPosition = objDom.offset().top,
                topListOffset = $('.mCustomScrollBox').offset().top,
                listHeigth = $('.mCustomScrollBox').height(),
                listViewPort = $('.mCustomScrollbar').height(),
                botListOffset = listHeigth + topListOffset,
                dropList = objDom.next(),
                dropListElemCount = objDom.next().children().length,
                botElemPosition = botListOffset - elemPosition,
                elemPositionInList = elemPosition - topListOffset;

            if ((botElemPosition <= dropListElemCount * 50) && (elemPositionInList > dropListElemCount * 40)) {
                dropList.addClass('margin-up');
            }

            if (listViewPort <= 400) {
                $('#mCSB_8_container')[0].style.paddingBottom = (dropListElemCount * 40) + "px";
            }
        },

        occupantPopover: function (objDom, e) {
            var id = objDom.data('id'),
                jid = QB.chat.helpers.getUserJid(id, QMCONFIG.qbAccount.appId),
                roster = ContactList.roster,
                position = e.currentTarget.getBoundingClientRect(),
                htmlTpl = QMHtml.User.occupantPopover({
                    'id': id,
                    'jid': jid
                }, roster[id]);

            $('body').append(htmlTpl);

            appearAnimation();

            objDom.addClass('is-active');
            $('.list-actions_occupants').offset({
                top: (29 + position.top),
                left: position.left
            });
        }
    };

    /* Private
    ---------------------------------------------------------------------- */
    var clearErrors = function() {
        $('.is-error').removeClass('is-error');
    };

    var switchPage = function(page) {
        $('body').removeClass('is-welcome');
        page.removeClass('is-hidden').siblings('section').addClass('is-hidden');

        // reset form
        clearErrors();
        $('.no-connection').addClass('is-hidden');
        page.find('input').val('');
        if (!page.is('#mainPage')) {
            page.find('form').removeClass('is-hidden').next('.l-form').remove(); // reset Forgot form after success sending of letter
            // page.find('input:file').prev().find('img').attr('src', QMCONFIG.defAvatar.url).siblings('span').text(QMCONFIG.defAvatar.caption);
            page.find('input:file').prev().find('.avatar').css('background-image', "url(" + QMCONFIG.defAvatar.url + ")").siblings('span').text(QMCONFIG.defAvatar.caption);
            page.find('input:checkbox').prop('checked', false);

            // start watch location if the option is enabled
            // if (localStorage['QM.latitude'] && localStorage['QM.longitude']) {
            //     localStorage.removeItem('QM.latitude');
            //     localStorage.removeItem('QM.longitude');
            //
            //     Location.toggleGeoCoordinatesToLocalStorage(true, function(res, err) {
            //         Helpers.log('Location: ', err ? err : res);
            //     });
            // }
        }
    };

    var appearAnimation = function() {
        $('.popover:not(.j-popover_const)').fadeIn(150);
    };

    return UserView;

});
