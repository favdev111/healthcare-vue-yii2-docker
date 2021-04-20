/*
 * Q-municate chat application
 *
 * Events Module
 *
 */
define([
    'jquery',
    'config',
    'Helpers',
    'QMHtml',
    //'LocationView',
    'minEmoji',
    'toastr',
    'mCustomScrollbar',
    'mousewheel'
], function(
    $,
    QMCONFIG,
    Helpers,
    QMHtml,
    //Location,
    minEmoji,
    toastr
) {

    iSstudentAdded = false;

    var Dialog,
        Cursor,
        UserView,
        DialogView,
        MessageView,
        AttachView;

    var chatName,
        editedChatName;


    toastr.options = {
        "preventDuplicates": true,
        "preventOpenDuplicates": true
    };

    var $workspace = $('.l-workspace-wrap');

    function Events(app) {
        this.app = app;

        Dialog = this.app.models.Dialog;
        Cursor = this.app.models.Cursor;
        User = this.app.models.User;
        UserView = this.app.views.User;
        ContactList = this.app.models.ContactList;
        ContactListView = this.app.views.ContactList;
        DialogView = this.app.views.Dialog;
        MessageView = this.app.views.Message;
        AttachView = this.app.views.Attach;
    }

    Events.prototype = {

        init: function() {
            window.isQMAppActive = true;

            $(window).focus(function() {
                var dialogItem,
                    dialog_id,
                    dialog;

                window.isQMAppActive = true;

                dialogItem = $('.l-list-wrap section .is-selected');
                dialog_id = dialogItem[0] && dialogItem.data('dialog');
                dialog = $('.dialog-item[data-dialog="' + dialog_id + '"] .contact');

                if ($('.dialog-item[data-dialog="' + dialog_id + '"]').hasClass('is-selected')) {
                    DialogView.htmlBuild(dialog, false);
                }

                if (dialog_id) {
                    dialogItem.find('.unread').text('');
                    DialogView.decUnreadCounter(dialog_id);
                }
            });

            $(window).blur(function() {
                var $chat = $('.l-chat:visible'),
                    $label = $('.l-chat:visible').find('.j-newMessages');

                if ($chat.length && $label.length) {
                    $label.remove();
                }

                window.isQMAppActive = false;
            });

            $(document).on('click', function(event) {
                clickBehaviour(event);
            });

            $('.popups').on('click', function(event) {
                var objDom = $(event.target);

                if (objDom.is('.popups') && !objDom.find('.popup.is-overlay').is('.is-open') || objDom.is('.attach-close')) {
                    closePopup();
                }
            });

            $(document).on('mouseenter', '[data-show-tooltip]', function() {
                $(this).next('.help-block').addClass('active');
            });
            $(document).on('click', '[data-hide-tooltip]', function() {
                $('.help-block').removeClass('active');
            });

            /* smiles
            ----------------------------------------------------- */
            $('.smiles-tab').on('click', function() {
                var $self = $(this),
                    group = $self.data('group');

                $self.addClass('is-actived')
                    .siblings().removeClass('is-actived');

                $('.smiles-group_' + group).removeClass('is-hidden')
                    .siblings().addClass('is-hidden');

                Cursor.setCursorToEnd($('.l-chat:visible .textarea')[0]);
            });

            $('.smiles-group').mCustomScrollbar({
                theme: 'minimal-dark',
                scrollInertia: 500,
                mouseWheel: {
                    scrollAmount: 'auto',
                    deltaFactor: 'auto'
                }
            });

            $workspace.on('click', '.j-em', function() {
                Cursor.setCursorAfterElement($(this)[0]);

                return false;
            });

            $('body').on('click', '#add-student-from-chat-link', function (e) {

                if (iSstudentAdded) {
                    return;
                }
                iSstudentAdded = true;
                // @todo Remove from parent window
                var toastr = window.toastr;
                $('#add-student-from-chat-link').prop("disabled", true);
                e.preventDefault();

                var chatCurUserId = $('.dialog-item.is-selected').data('id');
                if (!chatCurUserId) {
                    iSstudentAdded = false;
                    $('#add-student-from-chat-link').prop("disabled", false);
                    toastr.error('Please choose a student');
                    return;
                }
                $.ajax({
                    url: createUrlWithParams(App.endpoints.tutor.addStudent, [chatCurUserId]),
                    success: function (data) {
                        if (data.userIsUnverified) {
                            $('#add-student-from-chat-link').prop("disabled", false);
                            iSstudentAdded = false;
                            toastr.error('Please ask the student to add a payment method to their account.');
                            return;
                        }
                        if (data.errors) {
                            iSstudentAdded = false;

                            $('#add-student-from-chat-link').prop("disabled", false);
                            toastr.error('Student has already been added');
                            return;
                        }

                        toastr.success('Student successfully added');
                    }
                });
            });


            $('.j-em_wrap').on('click', function(event) {
                var target = $(this).children()[0],
                    textarea = $('.l-chat:visible .textarea')[0];

                if (target === event.target) {
                    textarea.focus();
                    Cursor.insertElement(target, 'j-em');
                } else {
                    Cursor.setCursorToEnd(textarea);
                }

                return false;
            });

            /* attachments
            ----------------------------------------------------- */
            $workspace.on('click', '.j-btn_input_attach', function(e) {
                e.stopPropagation();
                e.preventDefault();
                $(this).parents('.l-chat-footer')
                    .find('.attachment')
                    .click();
            });

            $workspace.on('change', '.attachment', function() {
                AttachView.changeInput($(this));
            });

            $workspace.on('click', '.attach-cancel', function(event) {
                event.preventDefault();
                AttachView.cancel($(this));
            });

            $workspace.on('click', '.attach-file', function (e) {
                e.preventDefault();
                var href = $(this).attr("href");
                window.open(href);
                return false;
            });

            $workspace.on('click', '.preview', function() {
                var $self = $(this),
                    name = $self.data('name'),
                    url = $self.data('url'),
                    attachType;

                if ($self.is('.preview-photo')) {
                    attachType = 'photo';
                    setAttachType(attachType);
                } else {
                    attachType = 'video';
                    setAttachType(attachType);
                }

                openAttachPopup($('#popupAttach'), name, url, attachType);
            });

            /* location
            ----------------------------------------------------- */
            // $workspace.on('click', '.j-send_location', function() {
            //     if (localStorage['QM.latitude'] && localStorage['QM.longitude']) {
            //         Location.toggleGeoCoordinatesToLocalStorage(false, function(res, err) {
            //             Helpers.log(err ? err : res);
            //         });
            //     } else {
            //         Location.toggleGeoCoordinatesToLocalStorage(true, function(res, err) {
            //             Helpers.log(err ? err : res);
            //         });
            //     }
            // });
            //
            // $workspace.on('mouseenter', '.j-showlocation', function() {
            //     $(this).find('.popover_map').fadeIn(150);
            // });
            //
            // $workspace.on('mouseleave', '.j-showlocation', function() {
            //     $(this).find('.popover_map').fadeOut(100);
            // });
            //
            // $workspace.on('click', '.j-btn_input_location', function() {
            //     var $self = $(this),
            //         $gmap = $('.j-popover_gmap'),
            //         bool = $self.is('.is-active');
            //
            //     removePopover();
            //
            //     if (!bool) {
            //         $self.addClass('is-active');
            //         $gmap.addClass('is-active');
            //
            //         Location.addMap($gmap);
            //     }
            //
            // });
            //
            // $workspace.on('click', '.j-send_map', function() {
            //     var localData = localStorage['QM.locationAttach'];
            //
            //     if (localData) {
            //         AttachView.sendMessage($('.l-chat:visible'), null, null, localData);
            //         localStorage.removeItem('QM.locationAttach');
            //         removePopover();
            //     }
            // });
            //
            // $('body').on('keydown', function(e) {
            //     if ((e.keyCode === 13) && $('.j-open_map').length) {
            //         $('.j-send_map').click();
            //     }
            // });

            /* group chats
            ----------------------------------------------------- */
            $workspace.on('click', '.groupTitle', function() {
                if ($('.l-chat:visible').find('.triangle_up').is('.is-hidden')) {
                    setTriagle('up');
                } else {
                    setTriagle('down');
                }
            });

            $workspace.on('click', '.groupTitle .addToGroupChat', function(event) {
                event.stopPropagation();
                var $self = $(this),
                    dialog_id = $self.data('dialog');

                Helpers.log('add people to groupchat');
                ContactListView.addContactsToChat($self, 'add', dialog_id);
            });

            $workspace.on('click', '.groupTitle .leaveChat, .groupTitle .avatar', function(event) {
                event.stopPropagation();
            });

            /* change the chat name
            ----------------------------------------------------- */
            $(document.body).on('click', function() {
                var $chat = $('.l-chat:visible');

                if ($chat.find('.groupTitle .name_chat').is('.is-focus')) {
                    $chat.find('.groupTitle .name_chat').removeClass('is-focus');
                    $chat.find('.groupTitle .name_chat')[0].scrollLeft = 0;
                    $chat.find('.triangle.is-hover').removeClass('is-hover')
                        .siblings('.pencil').addClass('is-hidden');

                    if (editedChatName && !editedChatName.name) {
                        $chat.find('.name_chat').text(chatName.name);
                    } else if (editedChatName && (editedChatName.name !== chatName.name) && (editedChatName.created_at > chatName.created_at)) {
                        $chat.find('.name_chat').text(editedChatName.name).attr('title', editedChatName.name);
                        Dialog.changeName($chat.data('dialog'), editedChatName.name);
                    } else {
                        $chat.find('.name_chat').text($chat.find('.name_chat').text().trim());
                    }
                }
            });

            $('body').on('click', '.groupTitle .name_chat', function(event) {
                event.stopPropagation();
                var $self = $(this);

                $self.addClass('is-focus');
                chatName = {
                    name: $self.text().trim(),
                    created_at: Date.now()
                };
                removePopover();
            });

            $('body').on('keyup', '.groupTitle .name_chat', function(event) {
                var $self = $(this),
                    code = event.keyCode;

                editedChatName = {
                    name: $self.text().trim(),
                    created_at: Date.now()
                };
                if (code === 13) {
                    $(document.body).click();
                    $self.blur();
                } else if (code === 27) {
                    editedChatName = null;
                    $self.text(chatName.name);
                    $(document.body).click();
                    $self.blur();
                }
            });

            /* scrollbars
            ----------------------------------------------------- */
            occupantScrollbar();

            /* button "back"
            ----------------------------------------------------- */
            $('.j-back_to_login_page').on('click', function() {
                UserView.loginQB();
                $('.j-success_callback').remove();
            });

            /* popovers
            ----------------------------------------------------- */

            $workspace.on('click', '.j-btn_input_smile', function() {
                var $self = $(this),
                    bool = $self.is('.is-active');

                removePopover();

                if (!bool) {
                    $self.addClass('is-active');
                    $('.j-popover_smile').addClass('is-active');
                }

                Cursor.setCursorToEnd($('.l-chat:visible .textarea')[0]);
            });

            /* popups
            ----------------------------------------------------- */

            $('#mainPage').on('click', '.createGroupChat', function(event) {
                event.preventDefault();

                Helpers.log('add people to groupchat');

                var $self = $(this),
                    isPrivate = $self.data('private');

                ContactListView.addContactsToChat($self, null, null, isPrivate);
            });

            $('.l-sidebar').on('click', '.addToGroupChat', function(event) {
                event.preventDefault();

                var $self = $(this),
                    dialog_id = $self.data('dialog');
                Helpers.log('add people to groupchat');
                ContactListView.addContactsToChat($self, 'add', dialog_id);
            });

            /* subscriptions
            ----------------------------------------------------- */
            $('.list_contacts').on('click', '.j-sendRequest', function() {
                var jid = $(this).parents('.j-listItem').data('jid');

                ContactListView.sendSubscribe(jid);
                Helpers.log('send subscribe');
            });

            $workspace.on('click', '.j-requestAgain', function() {
                var jid = $(this).parents('.j-chatItem').data('jid');

                ContactListView.sendSubscribe(jid, true);
                Helpers.log('send subscribe');
            });

            $('body').on('click', '.j-requestAction', function() {
                var jid = $(this).parents('.j-listItem').data('jid');

                ContactListView.sendSubscribe(jid);
                Helpers.log('send subscribe');
            });

            $('.list').on('click', '.j-requestConfirm', function() {
                var jid = $(this).parents('.j-incommingContactRequest').data('jid');

                ContactListView.sendConfirm(jid, true);
                Helpers.log('send confirm');
            });

            $('.list').on('click', '.j-requestCancel', function() {
                var jid = $(this).parents('.j-incommingContactRequest').data('jid');

                ContactListView.sendReject(jid, true);
                Helpers.log('send reject');
            });

            /* dialogs
            ----------------------------------------------------- */
            $('.list').on('click', '.contact', function(event) {
                $(this).find('.profileUserName').removeClass('text_weight');
                if (window.App.identity.isTutor === true) {
                    checkStudent = $(this).find('.avatar').data('id');
                }
                if (window.App.identity.isTutor == false) {
                    $('.list').find('.profile-data').hide();
                    $(this).find('.profile-data').show();
                }
                if (event.target.tagName !== 'INPUT') {
                    event.preventDefault();
                }
            });
            $(document).on('click', 'article .profileUserAvatar', function(event) {
                var chatUserId = $(this).data('id');
                var selectedUser = $('.dialog-item.is-selected');
                var selectedUserId = selectedUser.data('id');
                if (window.App.identity.isTutor === false && chatUserId === selectedUserId) {
                    window.open( selectedUser.data('profile-url'),'_blank');
                }
            });

            $('#popupContacts').on('click', '.contact', function() {
                var obj = $(this).parent(),
                    popup = obj.parents('.popup'),
                    len;

                if (obj.is('.is-chosen')) {
                    obj.removeClass('is-chosen').find('input').prop('checked', false);
                } else {
                    obj.addClass('is-chosen').find('input').prop('checked', true);
                }

                len = obj.parent().find('li.is-chosen').length;
                if (len === 1 && !popup.is('.is-addition')) {
                    popup.removeClass('not-selected');
                    popup.find('.btn_popup_private').removeClass('is-hidden').siblings().addClass('is-hidden');

                    if (obj.is('li:last')) popup.find('.list_contacts').mCustomScrollbar("scrollTo", "bottom");

                } else if (len >= 1) {
                    popup.removeClass('not-selected');
                    if (popup.is('.add'))
                        popup.find('.btn_popup_add').removeClass('is-hidden').siblings().addClass('is-hidden');
                    else
                        popup.find('.btn_popup_group').removeClass('is-hidden').siblings().addClass('is-hidden');

                    if (obj.is('li:last')) popup.find('.list_contacts').mCustomScrollbar("scrollTo", "bottom");

                } else {
                    popup.addClass('not-selected');
                }
            });

            $('.list_contextmenu').on('click', '.contact', function() {

                var contactId = $(this).parent('li').data('id');
                if (window.usersBlocked.indexOf(parseInt(contactId)) !== -1) {
                    var toastr = window.toastr;
                    toastr.error('Sorry, this user is currently blocked. Have a Question? Please contact us ' + window.App.global.defaultPhoneNumberFormatted);
                    return false;
                }

                DialogView.htmlBuild($(this));
                Cursor.setCursorToEnd($('.l-chat:visible .textarea')[0]);
            });

            $('#popupContacts .btn_popup_private').on('click', function() {
                var id = $('#popupContacts .is-chosen').data('id'),
                    dialogItem = $('.dialog-item[data-id="' + id + '"]').find('.contact');

                DialogView.htmlBuild(dialogItem);
            });

            $('body').on('click', '.writeMessage', function(event) {
                event.preventDefault();

                var id = $(this).data('id'),
                    dialogItem = $('.dialog-item[data-id="' + id + '"]').find('.contact');

                closePopup();
                DialogView.htmlBuild(dialogItem);
            });

            $('#popupContacts .btn_popup_group').on('click', function() {
                DialogView.createGroupChat();
            });

            $('#popupContacts .btn_popup_add').on('click', function() {
                var dialog_id = $(this).parents('.popup').data('dialog');
                DialogView.createGroupChat('add', dialog_id);
            });

            $workspace.on('click', '.j-btn_input_send', function() {
                var $msg = $('.j-message:visible');

                MessageView.sendMessage($msg);
                $msg.find('.textarea').val('');
                removePopover();
                Cursor.setCursorToEnd($('.l-chat:visible .textarea')[0]);
            });

            // show message status on hover event
            $('body').on('mouseenter', 'article.message.is-own', function() {
                var $self = $(this),
                    time = $self.find('.message-time'),
                    status = $self.find('.message-status');

                time.addClass('is-hidden');
                status.removeClass('is-hidden');
            });

            $('body').on('mouseleave', 'article.message.is-own', function() {
                var $self = $(this),
                    time = $self.find('.message-time'),
                    status = $self.find('.message-status');

                status.addClass('is-hidden');
                time.removeClass('is-hidden');
            });

            /* A button for the scroll to the bottom of chat
            ------------------------------------------------------ */
            $workspace.on('click', '.j-toBottom', function() {
                $('.l-chat:visible .scrollbar_message').mCustomScrollbar('scrollTo', 'bottom');
                $(this).hide();
            });

            $workspace.on('submit', '.j-message', function(event) {
                return false;
            });

            // $workspace.on('keyup', '.j-message', function() {
            //     var $textarea = $('.l-chat:visible .textarea'),
            //         $emj = $textarea.find('.j-em'),
            //         val = $textarea.text().trim();
            //
            //     if (val.length || $emj.length) {
            //         $textarea.addClass('contenteditable');
            //     } else {
            //         $textarea.removeClass('contenteditable').empty();
            //         Cursor.setCursorToEnd($textarea[0]);
            //     }
            // });

            // fix QMW-253
            // solution http://stackoverflow.com/questions/2176861/javascript-get-clipboard-data-on-paste-event-cross-browser
            $('body').on('paste', '.j-message', function(e) {
                var text = (e.originalEvent || e).clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);

                return false;
            });
        }
    };

    /* Private
    ---------------------------------------------------------------------- */
    function occupantScrollbar() {
        $('.chat-occupants, #popupIncoming').mCustomScrollbar({
            theme: 'minimal-dark',
            scrollInertia: 500,
            mouseWheel: {
                scrollAmount: 'auto',
                deltaFactor: 'auto'
            },
            live: true
        });
    }

    // Checking if the target is not an object run popover
    function clickBehaviour(e) {
        var objDom = $(e.target),
            selectors = '#profile, #profile *, .occupant, .occupant *, ' +
            '.j-btn_input_smile, .j-btn_input_smile *, .textarea, ' +
            '.textarea *, .j-popover_smile, .j-popover_smile *, ' +
            '.j-popover_gmap, .j-popover_gmap *, .j-btn_input_location, ' +
            '.j-btn_input_location *',
            googleImage = objDom.context.src && objDom.context.src.indexOf('/maps.gstatic.com/mapfiles/api-3/images/mapcnt6.png') || null;

        if (objDom.is(selectors) || e.which === 3 || googleImage === 7) {
            return false;
        } else {
            removePopover();
        }
    }

    function changeInputFile(objDom) {
        var URL = window.URL,
            file = objDom[0].files[0],
            src = file ? URL.createObjectURL(file) : QMCONFIG.defAvatar.url,
            fileName = file ? file.name : QMCONFIG.defAvatar.caption;

        objDom.prev().find('.avatar').css('background-image', "url(" + src + ")").siblings('span').text(fileName);
    }

    function removePopover() {
        var $openMap = $('.j-open_map');

        $('.is-contextmenu').removeClass('is-contextmenu');
        $('.is-active').removeClass('is-active');
        $('.popover').remove();

        if ($openMap.length) {
            $openMap.remove();
        }

        if ($('#mCSB_8_container').is(':visible')) {
            $('#mCSB_8_container')[0].style.paddingBottom = '0';
        }
    }

    function openPopup(objDom, id, dialog_id, isProfile) {
        // if it was the delete action
        if (id) {
            objDom.attr('data-id', id);
            objDom.find('#deleteConfirm').data('id', id);
        }
        // if it was the leave action
        if (dialog_id) {
            objDom.find('#leaveConfirm').data('dialog', dialog_id);
        }
        if (isProfile) {
            objDom.find('.popup-control-button_cancel').attr('data-isprofile', true);
        }
        objDom.add('.popups').addClass('is-overlay');
    }

    function openAttachPopup(objDom, name, url, attachType) {
        if (attachType === 'video') {
            objDom.find('video.attach-video').attr('src', url);
        } else {
            objDom.find('.attach-photo').attr('src', url);
        }

        objDom.find('.attach-name').text(name);
        objDom.find('.attach-download').attr('href', url).attr('download', name);
        objDom.add('.popups').addClass('is-overlay');
    }

    function closePopup() {
        $('.j-popupDelete.is-overlay').removeData('id');
        $('.is-overlay:not(.chat-occupants-wrap)').removeClass('is-overlay');
        $('.temp-box').remove();
        if ($('video.attach-video')[0]) $('video.attach-video')[0].pause();
    }

    function setAttachType(type) {
        var otherType = type === 'photo' ? 'video' : 'photo';

        $('.attach-' + type).removeClass('is-hidden')
            .siblings('.attach-' + otherType).addClass('is-hidden');
    }

    function setTriagle(UpOrDown) {
        var $chat = $('.l-chat:visible'),
            $triangle = $chat.find('.triangle_' + UpOrDown);

        $triangle.removeClass('is-hidden')
            .siblings('.triangle').addClass('is-hidden');

        $chat.find('.chat-occupants-wrap').toggleClass('is-overlay');
        $chat.find('.l-chat-content').toggleClass('l-chat-content_min');
    }

    return Events;

});
