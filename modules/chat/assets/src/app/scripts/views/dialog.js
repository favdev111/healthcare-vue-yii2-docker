/*
 * Q-municate chat application
 *
 * Dialog View Module
 *
 */
define([
    'jquery',
    'moment',
    'config',
    'quickblox',
    'Helpers',
    'QMHtml',
    'underscore',
    'models/person',
    'mCustomScrollbar',
    'nicescroll',

    'mousewheel'
], function(
    $,
    moment,
    QMCONFIG,
    QB,
    Helpers,
    QMHtml,
    _,
    Person
) {

    var User, Dialog, Message, ContactList;
    var unreadDialogs = {};
    var currentUser;

    function DialogView(app) {
        this.app = app;
        User = this.app.models.User;
        Dialog = this.app.models.Dialog;
        Message = this.app.models.Message;
        ContactList = this.app.models.ContactList;
    }

    DialogView.prototype = {

        // QBChat handlers
        chatCallbacksInit: function() {
            var self = this;

            var MessageView = this.app.views.Message;

            QB.chat.onMessageListener         = MessageView.onMessage;
            QB.chat.onSystemMessageListener   = MessageView.onSystemMessage;
            QB.chat.onDeliveredStatusListener = MessageView.onDeliveredStatus;
            QB.chat.onReadStatusListener      = MessageView.onReadStatus;

            QB.chat.onDisconnectedListener = function() {
                if ('div.popups.is-overlay') {
                    $('.is-overlay:not(.chat-occupants-wrap)').removeClass('is-overlay');
                }
                $('.j-disconnect').addClass('is-overlay')
                    .parent('.j-overlay').addClass('is-overlay');
            };

            QB.chat.onReconnectListener = function() {
                $('.j-disconnect').removeClass('is-overlay')
                    .parent('.j-overlay').removeClass('is-overlay');
            };

            QB.chat.onReconnectFailedListener = function(error) {
                if (error) {
                    self.app.service.reconnectChat();
                }
            };

            currentUser = new Person(_.clone(User.contact), {
                app: this.app,
                parse: true
            });
        },

        createDataSpinner: function(chat, groupchat, isAjaxDownloading) {
            this.removeDataSpinner();
            //default chat spinner
            // var spinnerBlock;
            // if (isAjaxDownloading) {
            //     spinnerBlock = '<div class="message message_service"><div class="popup-elem spinner_bounce is-empty is-ajaxDownload">';
            // } else if (groupchat) {
            //     spinnerBlock = '<div class="popup-elem spinner_bounce is-creating">';
            // } else {
            //     spinnerBlock = '<div class="popup-elem spinner_bounce is-empty">';
            // }
            //
            // spinnerBlock += '<div class="spinner__item"></div>';
            // // spinnerBlock += '<div class="spinner_bounce-bounce2"></div>';
            // // spinnerBlock += '<div class="spinner_bounce-bounce3"></div>';
            // spinnerBlock += '</div>';
            //
            // if (isAjaxDownloading) spinnerBlock += '</div>';
            //
            // if (chat) {
            //     $('.l-chat:visible').find('.l-chat-content').append(spinnerBlock);
            // } else if (groupchat) {
            //     $('#popupContacts .btn_popup').addClass('is-hidden');
            //     $('#popupContacts .popup-footer').append(spinnerBlock);
            //     $('#popupContacts .popup-footer').after('<div class="temp-box"></div>');
            // } else if (isAjaxDownloading) {
            //     $('.l-chat:visible').find('.l-chat-content').prepend(spinnerBlock);
            // } else {
            //     $('#emptyList').after(spinnerBlock);
            // }
        },

        removeDataSpinner: function() {
            $('.spinner_bounce, .temp-box, div.message_service').remove();
        },

        prepareDownloading: function(roster) {
            Helpers.log('QB SDK: Roster has been got', roster);
            this.chatCallbacksInit();
            this.createDataSpinner();
            scrollbar();
            ContactList.saveRoster(roster);

            this.app.models.SyncTabs.init(User.contact.id);
        },

        getUnreadCounter: function(dialog_id) {
            var counter;

            if (typeof unreadDialogs[dialog_id] === 'undefined') {
                unreadDialogs[dialog_id] = true;
                counter = Object.keys(unreadDialogs).length;
            }
            this.setCounter();
        },

        decUnreadCounter: function(dialog_id) {
            var counter;

            if (typeof unreadDialogs[dialog_id] !== 'undefined') {
                delete unreadDialogs[dialog_id];
                counter = Object.keys(unreadDialogs).length;
            }
            this.setCounter();
        },

        setCounter: function () {
            var QBApiCalls = this.app.service;
            QBApiCalls.listDialogs({}, function (items) {
                var count = 0;
                $.ajax({
                    url: App.endpoints.blockedUsers,
                    success: function (users) {
                        for (var i = 0; i < users.length; i++) {
                            if (usersBlocked.indexOf(parseInt(users[i])) === -1) {
                                usersBlocked.push(parseInt(users[i]));
                            }
                        }
                        $.each(items, function (index, item) {
                            var tutorIdDialog = item.occupants_ids[1];
                            var participants = item.occupants_ids;
                            var studentIndex = participants.indexOf(parseInt(App.chat.user.chatId));
                            if (studentIndex == 1) {
                                tutorIdDialog = item.occupants_ids[0];
                            }
                            if (usersBlocked.indexOf(tutorIdDialog) === -1) {
                                count += item.unread_messages_count;
                            }
                        });
                        if (count) {
                            $('.badge-count-message', window.document).text(count);
                        } else {
                            $('.badge-count-message', window.document).text('');
                        }
                        parent.getTotalUnreadItems();
                    }
                });
            });
        },

        logoutWithClearData: function() {
            unreadDialogs = {};
            $('.mediacall-remote-duration').text('connecting...');
            $('.mediacall-info-duration').text('');
        },

        downloadDialogs: function(roster, ids) {
            var self = this,
                hiddenDialogs = sessionStorage['QM.hiddenDialogs'] ? JSON.parse(sessionStorage['QM.hiddenDialogs']) : {},
                rosterIds = Object.keys(roster),
                notConfirmed,
                private_id,
                dialog,
                occupants_ids,
                chat;

            Dialog.download(function(dialogs) {
                self.removeDataSpinner();

                if (dialogs.length > 0) {

                    occupants_ids = _.uniq(_.flatten(_.pluck(dialogs, 'occupants_ids'), true));

                    // updating of Contact List whereto are included all people
                    // with which maybe user will be to chat (there aren't only his friends)
                    var userHash = false;
                    ContactList.add(occupants_ids, null, function() {
                        for (var i = 0, len = dialogs.length; i < len; i++) {
                            var tutorIdDialog = dialogs[i].occupants_ids[1];
                            var participants = dialogs[i].occupants_ids;
                            var studentIndex = participants.indexOf(parseInt(App.chat.user.chatId));
                            if (studentIndex == 1) {
                                tutorIdDialog = dialogs[i].occupants_ids[0];
                            }
                            dialog = Dialog.create(dialogs[i]);

                            ContactList.dialogs[dialog.id] = dialog;

                            // don't create a duplicate dialog in contact list
                            chat = $('.l-list-wrap section .dialog-item[data-dialog="' + dialog.id + '"]');
                            if (chat[0] && dialog.unread_count) {
                                chat.find('.unread').text(dialog.unread_count);
                                self.getUnreadCounter(dialog.id);
                                continue;
                            }

                            if (dialog.type === 2) QB.chat.muc.join(dialog.room_jid);

                            // update hidden dialogs
                            private_id = dialog.type === 3 ? dialog.occupants_ids[0] : null;
                            hiddenDialogs[private_id] = dialog.id;
                            ContactList.saveHiddenDialogs(hiddenDialogs);

                            // not show dialog if user has not confirmed this contact
                            // notConfirmed = localStorage['QM.notConfirmed'] ? JSON.parse(localStorage['QM.notConfirmed']) : {};
                            // if (private_id && (!roster[private_id] ||
                            //     (roster[private_id] && roster[private_id].subscription === 'none' &&
                            //     !roster[private_id].ask && notConfirmed[private_id]))) {
                            //     continue;
                            // }

                            self.addDialogItem(dialog, true);
                            if (window.location.hash.substring(1) == private_id) {
                                userHash = private_id;
                            }
                        }


                        if ($('#requestsList').is('.is-hidden') &&
                            $('#recentList').is('.is-hidden')) {

                            $('#emptyList').removeClass('is-hidden');
                        }
                        if (userHash) {
                            // TODO: find out why without timout there is a duplicated message (@see HEYTUTOR-3330)
                            setTimeout(function () {
                                $('li.list-item[data-id="'+userHash+'"]').click();
                                $('li.list-item[data-id="'+userHash+'"] .contact').click();
                            }, 500);
                        }

                    });

                } else {
                    $('#emptyList').removeClass('is-hidden');
                }

                // self.getAllUsers(rosterIds);
                self.getAllUsers(occupants_ids);
            });
        },

        getAllUsers: function(rosterIds) {
            var QBApiCalls = this.app.service,
                Contact = this.app.models.Contact,
                ContactList = this.app.models.ContactList,
                params = {
                    filter: {
                        field: 'id',
                        param: 'in',
                        value: rosterIds
                    },
                    per_page: 100
                };

            QBApiCalls.listUsers(params, function(users) {
                users.items.forEach(function(qbUser) {
                    var user = qbUser.user;
                    var contact = Contact.create(user);
                    ContactList.contacts[contact.id] = contact;

                    $('.profileUserName[data-id="' + contact.id + '"]').text(contact.full_name);
                    $('.profileUserStatus[data-id="' + contact.id + '"]').text(contact.status);
                    $('.profileUserPhone[data-id="' + contact.id + '"]').html(
                        '<span class="userDetails-label">Phone:</span><span class="userDetails-phone">' + contact.phone + '</span>'
                    );
                    $('.profileUserAvatar[data-id="' + contact.id + '"]').css('background-image', 'url(' + contact.avatar_url + ')');

                    localStorage.setItem('QM.contact-' + contact.id, JSON.stringify(contact));
                });
            });
        },

        hideDialogs: function() {
            $('.l-list').addClass('is-hidden');
            $('.l-list ul').html('');
        },

        addDialogItem: function(dialog, isDownload, isNew) {

            var contacts = ContactList.contacts,
                roster = ContactList.roster,
                private_id, icon, name, status,
                html, startOfCurrentDay,
                self = this;

            private_id = dialog.type === 3 ? dialog.occupants_ids[0] : null;

            if (private_id && !contacts[private_id]) {
                return;
            }

            try {
                name = private_id ? contacts[private_id].full_name : dialog.room_name;
                icon = private_id ? contacts[private_id].avatar_url : (dialog.room_photo || QMCONFIG.defAvatar.group_url);
                status = roster[private_id] ? roster[private_id] : null;
            } catch (error) {
                console.error(error);
            }

            var isUnreadTextShown = (window.usersBlocked.indexOf(private_id) === -1) && dialog.unread_count;
            html = '<li class="list-item dialog-item presence-listener" data-dialog="' + dialog.id + '" data-id="' + private_id + '">';
            html += '<a class="contact l-flexbox" href="#">';
            html += '<div class="l-flexbox_inline">';
            html += '<div class="contact-avatar avatar profileUserAvatar" style="background-image:url(' + icon + ')" data-id="' + private_id + '"></div>';
            html += '<div class="message-list-info">';
            html += '<h4 class="chat-list_title"><span class="name profileUserName '+(isUnreadTextShown ?'text_weight' : '')+'" data-id="' + private_id + '">' + name + '</span></h4>';
            if (dialog.last_message_date_sent) {
                html += '<div class="time-rate"><time class="message-info_time" data-time="' + dialog.last_message_date_sent + '">' + moment.unix(parseInt(dialog.last_message_date_sent, 10)).fromNow() + '</time>';
            }
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // if (dialog.type === 3) {
            //     html = getStatus(status, html);
            // } else {
            //     html += '<span class="status"></span>';
            // }

            // html += '<span class="unread">' + dialog.unread_count + '</span>';
            html += '</a></li>';

            startOfCurrentDay = new Date();
            startOfCurrentDay.setHours(0, 0, 0, 0);

            // checking if this dialog is recent OR no
            if (isDownload) {
                $('#recentList').removeClass('is-hidden').find('ul').append(html);
            } else {
                $('#recentList').removeClass('is-hidden').find('ul').prepend(html);
            }

            $('#emptyList').addClass('is-hidden');
            if (dialog.unread_count) {
                self.getUnreadCounter(dialog.id);
            }
        },

        htmlBuild: function(objDom, scroll) {
            if (scroll === undefined) {
                scroll = true;
            }
            var MessageView = this.app.views.Message,
                contacts = ContactList.contacts,
                dialogs = ContactList.dialogs,
                roster = ContactList.roster,
                parent = objDom.parent(),
                dialog_id = parent.data('dialog'),
                user_id = parent.data('id'),
                dialog = dialogs[dialog_id],
                user = contacts[user_id],
                $chat = $('.l-chat[data-dialog="' + dialog_id + '"]'),
                readBadge = 'QM.' + User.contact.id + '_readBadge',
                unreadCount = Number(objDom.find('.unread').text()),
                self = this,
                html,
                jid,
                icon,
                name,
                status,
                msgArr,
                userId,
                messageId,
                private_id;

            jid = dialog.room_jid || user.user_jid;
            icon = user_id ? user.avatar_url : (dialog.room_photo || QMCONFIG.defAvatar.group_url);
            name = dialog.room_name || user.full_name;
            status = roster[user_id] ? roster[user_id] : null;
            private_id = dialog.type === 3 ? dialog.occupants_ids[0] : null;

            if ($chat.length === 0) {
                if (dialog.type === 3) {
                    html = '<section class="l-workspace l-chat l-chat_private presence-listener j-chatItem" data-dialog="' + dialog_id + '" data-id="' + user_id + '" data-jid="' + jid + '">';
                    // html += '<header class="l-chat-header l-flexbox l-flexbox_flexbetween">';
                } else {
                    html = '<section class="l-workspace l-chat l-chat_group is-group j-chatItem" data-dialog="' + dialog_id + '" data-jid="' + jid + '">';
                    // html += '<header class="l-chat-header l-flexbox l-flexbox_flexbetween groupTitle">';
                }

                html += '<div class="l-workspace-info l-flexbox l-flexbox_column">\n';

                var notificationMessage = '';
                if (window.App.identity.isTutor == false) {
                    html += '<div class="panel-tutor-info l-flexbox">' +
                        '<a href="" target="_blank" data-profile-url><div class="contact-avatar avatar profileUserAvatar click-avatar"></div></a>' +
                        '<h4 class="profile-data-name"></h4>' +
                        '<div class="profile-data-rating"></div>' +
                        '<div class="profile-data-distance"></div>' +
                        '<div class="profile-data-rate"></div>' +
                        '</div>\n';

                    if (App.user && (App.user.hasVerifiedPaymentMethod === false)) {
                        var notificationMessageUrl = createUrlWithParams(App.endpoints.student.paymentInfo, null, { 'hideUnverifiedBanner': true }, 'tabAddPayment');
                        notificationMessage = 'Please&nbsp;<a href="' + notificationMessageUrl + '" target="_top"> add payment</a>&nbsp;information to verify your account & hire tutors.';
                    }
                }

                html += '<div class="panel-notification-info l-flexbox l-flexbox_flexcenter' + (notificationMessage ? '' : ' is-hidden') + '">' + notificationMessage + '</div>' +
                    '</div>';

                // build occupants of room
                if (dialog.type === 2) {
                    html += '<div class="chat-occupants-wrap">';
                    html += '<div class="chat-occupants">';
                    for (var i = 0, len = dialog.occupants_ids.length, id; i < len; i++) {
                        id = dialog.occupants_ids[i];
                        if (id != User.contact.id) {
                            html += '<a class="occupant l-flexbox_inline presence-listener" data-id="' + id + '" href="#">';

                            html = getStatus(roster[id], html);

                            html += '<span class="name name_occupant">' + contacts[id].full_name + '</span>';
                            html += '</a>';
                        }
                    }
                    html += '</div></div>';
                }

                html += '<section class="l-chat-content scrollbar_message"></section>';
                html += QMHtml.Dialogs.setTextarea();

                html += '</section>';

                $('.l-workspace-wrap .l-workspace').addClass('is-hidden').parent().append($(html));
                if (window.App.identity.isTutor == false) {
                    $('.l-workspace.l-chat').addClass('has-notification-student');
                }

                textAreaScrollbar();

                if (dialog.type === 3 && (!status || status.subscription === 'none'))
                    $('.l-chat:visible').addClass('is-request');

                self.createDataSpinner(true);
                self.showChatWithNewMessages(dialog_id, unreadCount);
            } else {
                $chat.removeClass('is-hidden')
                     .siblings(':not(.j-popover_const)').addClass('is-hidden');
                $('.l-chat:visible .scrollbar_message').mCustomScrollbar('destroy');
                self.messageScrollbar();

                if (typeof dialog.messages !== "undefined" && dialog.messages.length > 0 && dialog.type == 3) {
                    Message.update(dialog.messages.join(), dialog_id, user_id);
                }
                if (typeof dialog.messages !== "undefined" && dialog.messages.length > 0 && dialog.type == 2) {
                    for (var j = 0, ln = dialog.messages.length; j < ln; j++) {
                        messageId = dialog.messages[j];
                        userId = $('#' + messageId).data('id');
                        QB.chat.sendReadStatus({
                            messageId: messageId,
                            userId: userId,
                            dialogId: dialog_id
                        });

                        $.ajax({
                            url: createUrlWithParams(App.endpoints.chat.markRead, [messageId, dialog_id]),
                            method: 'POST',
                        });
                    }

                    Message.update(null, dialog_id);
                }
            }

            if (window.App.identity.isTutor === false) {
                $.ajax({
                    url: createUrlWithParams(App.endpoints.chat.getTutorData, [private_id]),
                    success: function (ans) {
                        $('.dialog-item[data-id="'+private_id+'"]').attr('data-profile-url', ans.url);
                        $('.l-workspace-wrap > .l-chat > .l-workspace-info .panel-tutor-info a[data-profile-url]').attr('href', ans.url);
                        var rating = '';
                        if (parseInt(ans.rating) > 0) {
                            rating = '<div class="flex-media__rate"><span class="fw-semi">' + ans.rating + '</span>' +
                                '<div class="rating-container rating-animate rating-disabled">' +
                                '<div class="rating">' +
                                '<span class="empty-stars">' +
                                '<span class="star">' +
                                '<i class="fa fa-star star-grey"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-grey"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-grey"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-grey"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-grey"></i>' +
                                '</span>' +
                                '</span>' +
                                '<span class="filled-stars" style="width: ' + (ans.rating / 0.05) + '%">' +
                                '<span class="star">' +
                                '<i class="fa fa-star star-yellow"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-yellow"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-yellow"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-yellow"></i>' +
                                '</span><span class="star">' +
                                '<i class="fa fa-star star-yellow"></i>' +
                                '</span></span></div></div></div>';
                        }

                        var $wrap = $('.l-workspace-wrap > .l-chat > .l-workspace-info .panel-tutor-info');

                        $('.profileUserAvatar', $wrap).css('backgroundImage', 'url(' + icon + ')');
                        $('.profile-data-name', $wrap).text(name);
                        $('.profile-data-rating', $wrap).html(rating);
                        if (ans.distance != 0) {
                            $('.profile-data-distance', $wrap).html(ans.distance + ' miles from you');
                        }
                    },
                    error: function () {

                    }
                });
            } else {
                $('.l-workspace.l-chat').removeClass('has-notification-tutor');
                $('.l-workspace.l-chat .l-workspace-info .panel-notification-info').addClass('is-hidden');
                $.ajax({
                    url: createUrlWithParams(App.endpoints.chat.checkStudentCard, [private_id]),
                    success: function (ans) {
                        var $panelInfo = $('.l-workspace.l-chat .l-workspace-info .panel-notification-info');
                        if (ans.errors) {
                            $('.l-workspace.l-chat').addClass('has-notification-tutor');
                            $panelInfo.removeClass('is-hidden').text('This student is unverified. Contact information cannot be shared.');
                        }
                    }
                });
            }

            removeNewMessagesLabel($('.is-selected').data('dialog'), dialog_id);
            $('.is-selected').removeClass('is-selected');
            parent.addClass('is-selected').find('.unread').text('');
            self.decUnreadCounter(dialog.id);
            if (scroll) {
                setScrollToNewMessages();
            }
            // set dialog_id to localStorage wich must bee read in all tabs for same user
            localStorage.removeItem(readBadge);
            localStorage.setItem(readBadge, dialog_id);
        },

        messageScrollbar: function() {
            var $objDom = $('.l-chat:visible .scrollbar_message'),
                height = $objDom[0].scrollHeight,
                self = this;

            $objDom.mCustomScrollbar({
                theme: 'minimal-dark',
                scrollInertia: 'auto',
                mouseWheel: {
                    scrollAmount: 120,
                    deltaFactor: 'auto'
                },
                setTop: height + 'px',
                callbacks: {
                    onTotalScrollBack: function() {
                        ajaxDownloading($objDom, self);
                    },
                    onTotalScroll: function() {

                        var isBottom = Helpers.isBeginOfChat(),
                            $currentDialog = $('.dialog-item.is-selected'),
                            dialogId = $currentDialog.data('dialog');

                        if (isBottom) {
                            $('.j-toBottom').hide();
                            $currentDialog.find('.unread').text('');
                            self.decUnreadCounter(dialogId);
                        }
                    },
                    onScroll: function() {
                        var isBottom = Helpers.isBeginOfChat();
                        if (!isBottom) {
                            $('.j-toBottom').show();
                        }
                    }
                },
                live: true
            });

        },

        createGroupChat: function(type, dialog_id) {
            var contacts = ContactList.contacts,
                new_members = $('#popupContacts .is-chosen'),
                occupants_ids = $('#popupContacts').data('existing_ids') || [],
                groupName = occupants_ids.length > 0 ? [User.contact.full_name, contacts[occupants_ids[0]].full_name] : [User.contact.full_name],
                occupants_names = !type && occupants_ids.length > 0 ? [contacts[occupants_ids[0]].full_name] : [],
                self = this,
                new_ids = [],
                new_id, occupant,
                roster = ContactList.roster,
                chat = $('.l-chat[data-dialog="' + dialog_id + '"]');

            for (var i = 0, len = new_members.length, name; i < len; i++) {
                name = $(new_members[i]).find('.name').text();
                if (groupName.length < 3) groupName.push(name);
                occupants_names.push(name);
                occupants_ids.push($(new_members[i]).data('id').toString());
                new_ids.push($(new_members[i]).data('id').toString());
            }

            groupName = groupName.join(', ');
            occupants_names = occupants_names.join(', ');
            occupants_ids = occupants_ids.join();

            self.createDataSpinner(null, true);
            if (type) {
                Dialog.updateGroup(occupants_names, {
                    dialog_id: dialog_id,
                    occupants_ids: occupants_ids,
                    new_ids: new_ids
                }, function(dialog) {
                    self.removeDataSpinner();
                    var dialogItem = $('.l-list-wrap section .dialog-item[data-dialog="' + dialog.id + '"]');
                    if (dialogItem.length > 0) {
                        copyDialogItem = dialogItem.clone();
                        dialogItem.remove();
                        $('#recentList ul').prepend(copyDialogItem);
                    }
                    $('.is-overlay:not(.chat-occupants-wrap)').removeClass('is-overlay');
                });
            } else {
                Dialog.createGroup(occupants_names, {
                    name: groupName,
                    occupants_ids: occupants_ids,
                    type: 2
                }, function(dialog) {
                    self.removeDataSpinner();
                    $('.is-overlay:not(.chat-occupants-wrap)').removeClass('is-overlay');
                    $('.dialog-item[data-dialog="' + dialog.id + '"]').find('.contact').click();
                });
            }
        },

        leaveGroupChat: function(objDom) {
            var dialogs = ContactList.dialogs,
                dialog_id = objDom.data('dialog'),
                dialog = dialogs[dialog_id],
                li = $('.dialog-item[data-dialog="' + dialog_id + '"]'),
                chat = $('.l-chat[data-dialog="' + dialog_id + '"]'),
                list = li.parents('ul');

            Dialog.leaveChat(dialog, function() {
                li.remove();
                isSectionEmpty(list);

                // delete chat section
                if (chat.is(':visible')) {
                    $('#capBox').removeClass('is-hidden');
                }
                if (chat.length > 0) {
                    chat.remove();
                }
                delete dialogs[dialog_id];
            });

        },

        showChatWithNewMessages: function(dialogId, unreadCount) {
            var MessageView = this.app.views.Message,
                self = this,
                lastReaded,
                message,
                count;

            var MIN_STACK = 20,
                MAX_STACK = 100,
                lessThenMinStack = unreadCount < MIN_STACK,
                moreThenMinStack = unreadCount > (MIN_STACK - 1),
                lessThenMaxStack = unreadCount < MAX_STACK;

            if (lessThenMinStack) {
                lastReaded = unreadCount;
            } else if (moreThenMinStack && lessThenMaxStack) {
                lastReaded = unreadCount;
                count = unreadCount + 1;
            } else {
                lastReaded = MAX_STACK - 1;
                count = MAX_STACK;
            }

            Message.download(dialogId, function(messages) {
                for (var i = 0, len = messages.length; i < len; i++) {
                    message = Message.create(messages[i]);
                    message.stack = Message.isStack(false, messages[i], messages[i + 1]);

                    if ((message.read_ids.length < 2 && message.sender_id != User.contact.id)) {
                        QB.chat.sendReadStatus({
                            messageId: message.id,
                            userId: message.sender_id,
                            dialogId: dialogId
                        });
                    }

                    // if we have recent unread messages and user came from dashboard
                    // to dialog page (location.hash will not be empty in this case) we should mark first message as read even if it's already marked.
                    // It should mark all recent unread messages as read
                    if ((message.read_ids.length < 2 && message.sender_id != User.contact.id) || (i === 0 && location.hash && message.recipient_id == App.chat.user.chatId)) {
                        $.ajax({
                            url: createUrlWithParams(App.endpoints.chat.markRead, [message.id, dialogId]),
                            method: 'POST',
                        });
                    }


                    if (unreadCount) {
                        switch (i) {
                            case (lastReaded - 1):
                                message.stack = false;
                                break;
                            case lastReaded:
                                setLabelForNewMessages(dialogId);
                                break;
                            default:
                                break;
                        }
                    }

                    MessageView.addItem(message, null, null, message.recipient_id);

                    self.messageScrollbar();

                    if (i === (len - 1)) {
                        setScrollToNewMessages();
                        self.removeDataSpinner();
                    }
                }
            }, count);

            Message.update(null, dialogId);
        }

    };

    /* Private
    ---------------------------------------------------------------------- */
    function scrollbar() {
        $('.l-sidebar .scrollbar').mCustomScrollbar({
            theme: 'minimal-dark',
            scrollInertia: 150,
            mouseWheel: {
                scrollAmount: 100,
                deltaFactor: 0
            },
            live: true
        });
    }

    // ajax downloading of data through scroll
    function ajaxDownloading($chat, self) {
        var MessageView = self.app.views.Message,
            dialog_id = $chat.parents('.l-chat').data('dialog'),
            count = $chat.find('.message').length,
            message;

        var listHeightBefore = $chat.find('.mCSB_container').height(),
            draggerHeightBefore = $chat.find('.mCSB_dragger').height(),
            viewPort = $chat.find('.mCustomScrollBox').height();

        Message.download(dialog_id, function(messages) {
            for (var i = 0, len = messages.length; i < len; i++) {
                message = Message.create(messages[i]);
                message.stack = Message.isStack(false, messages[i], messages[i + 1]);

                MessageView.addItem(message, true);

                if ((i + 1) === len) {
                    var listHeightAfter = $chat.find('.mCSB_container').height(),
                        draggerHeightAfter = $chat.find('.mCSB_dragger').height(),
                        thisStopList = listHeightBefore - listHeightAfter,
                        thisStopDragger = (draggerHeightAfter / (draggerHeightBefore + draggerHeightAfter)) * viewPort;

                    $('.l-chat-content .mCSB_container').css({
                        top: thisStopList + 'px'
                    });
                    $('.l-chat-content .mCSB_dragger').css({
                        top: thisStopDragger + 'px'
                    });
                }
            }
        }, count, 'ajax');
    }

    function openPopup(objDom) {
        objDom.add('.popups').addClass('is-overlay');
    }

    function getStatus(status, html) {
        if (!status || status.subscription === 'none') {
            html += '<span class="status status_request"></span>';
        } else if (status && status.status) {
            html += '<span class="status status_online"></span>';
        } else {
            html += '<span class="status"></span>';
        }

        return html;
    }

    function textAreaScrollbar() {
        $('.l-chat:visible .textarea').niceScroll({
            cursoropacitymax: 0.5,
            railpadding: {
                right: -13
            },
            zindex: 1,
            enablekeyboard: false
        });
    }

    function isSectionEmpty(list) {
        if (list.contents().length === 0) {
            list.parent().addClass('is-hidden');
        }

        if ($('#requestsList').is('.is-hidden') &&
            $('#recentList').is('.is-hidden')) {

            $('#emptyList').removeClass('is-hidden');
        }
    }

    function setLabelForNewMessages(dialogId) {
        var $chatContainer = $('.l-chat[data-dialog="' + dialogId + '"]').find('.l-chat-content .mCSB_container'),
            $newMessages = $('<div class="new_messages j-newMessages" data-dialog="' + dialogId + '">' +
                '<span class="newMessages">New messages</span></div>');

        $chatContainer.prepend($newMessages);
    }

    function setScrollToNewMessages() {
        var $chat = $('.l-chat:visible .scrollbar_message'),
            isBottom = Helpers.isBeginOfChat(),
            isScrollDragger = $chat.find('.mCSB_draggerContainer').length;

        if ($('.j-newMessages').length) {
            $chat.mCustomScrollbar('scrollTo', '.j-newMessages');
        } else {
            $chat.mCustomScrollbar('scrollTo', 'bottom');
        }

        if (!isBottom && isScrollDragger) {
            $('.j-toBottom').show();
        }
    }

    function removeNewMessagesLabel(dialogId, curDialogId) {
        var $label = $('.j-newMessages[data-dialog="' + dialogId + '"]');

        if ($label.length && (dialogId !== curDialogId)) {
            $label.remove();
        }
    }

    return DialogView;

});
