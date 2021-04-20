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
    var email_regex = /([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi;

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
            QB.chat.onMessageTypingListener   = MessageView.onMessageTyping;
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
                $.each(items, function (index, item) {
                    count += item.unread_messages_count;
                });
                if (count) {
                    $('.badge-count-message').text(count);
                } else {
                    $('.badge-count-message').text('');
                }
                parent.getTotalUnreadItems();
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
                            dialog.lastMessage = dialogs[i].last_message;
                            dialog.lastMessageSenderId = dialogs[i].last_message_user_id;
                            for (var j = 0; j < 2; j++) {
                                if (dialogs[i].occupants_ids[j] !== dialog.lastMessageSenderUserId) {
                                    dialog.lastMessageRecepientId = dialogs[i].occupants_ids[j];
                                }

                            }

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
                            if (location.hash.substring(1) == private_id) {
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

            if (tutor == false) {
                $.ajax({
                    url: createUrlWithParams(App.endpoints.chat.getTutorData, [private_id]),
                    success: function (ans) {
                        $('.dialog-item[data-id="'+private_id+'"]').attr('data-profile-url', ans.url);
                    },
                    error: function () {

                    }
                });
            }

            // var nameFull = name.split(' ');
            // var lastName = nameFull[0] || '';
            // var firstName = nameFull[1] || '';
            // name = firstName+' '+(lastName? lastName[0]+'.' : '');
            // console.log(name);
            var lastMessage = dialog.lastMessage || '';
            lastMessage = lastMessage.replace(/<\/b>/gi, '');
            lastMessage = lastMessage.replace(/<b>/gi, '');
            var slicedMessage = lastMessage.slice(0,70);
            if (slicedMessage.length < lastMessage.length) {
                slicedMessage += '...';
            }
            if ((typeof dialog.lastMessageRecepientId !==  'undefined') && App.chat.user.chatId === dialog.lastMessageRecepientId) {
                if (
                    window.parent.unverifiedStudents.indexOf(dialog.lastMessageSenderId) !== -1
                    || window.parent.unverifiedStudents.indexOf(dialog.lastMessageRecepientId) !== -1
                ) {
                    slicedMessage = slicedMessage.replace(email_regex, '*****');
                    slicedMessage = Helpers.replaceForbiddenSymbols(slicedMessage);
                    App.mailDomains.forEach(function(item, i, arr) {
                        slicedMessage = slicedMessage.replace(new RegExp(item, 'i'), '*****');
                    });
                }
            }

            if (window.usersBlocked.indexOf(private_id) !== -1) {
                slicedMessage = 'This user is blocked';
            }

            var isUnreadTextShown = (window.parent.usersBlocked.indexOf(private_id) === -1) && dialog.unread_count;
            html = '<li class="list-item dialog-item presence-listener" data-dialog="' + dialog.id + '" data-id="' + private_id + '">';
            html += '<a class="contact l-flexbox" href="#">';
            html += '<div class="l-flexbox_inline">';
            html += '<div class="contact-avatar avatar profileUserAvatar" style="background-image:url(' + icon + ')" data-id="' + private_id + '"></div>';
            html += '<div class="message-list-info">';
            html += '<h4 class="chat-list_title l-flexbox_inline l-flexbox_alignstretch"><span class="name profileUserName '+(isUnreadTextShown ?'text_weight' : '')+'" data-id="' + private_id + '">' + name + '</span>';
            if (dialog.last_message_date_sent) {
                html += moment.unix(parseInt(dialog.last_message_date_sent, 10)).fromNow();
            }
            html += '</time></h4>';
            html += '<div class="last-message-dialog' + (dialog.unread_count ? ' unread-dialog-message' : '') + '">';
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

            var selector = '.dialog-item[data-dialog="' + dialog.id.toString() + '"] .last-message-dialog';
            $(selector).text(slicedMessage)

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
                messageId;

            jid = dialog.room_jid || user.user_jid;
            icon = user_id ? user.avatar_url : (dialog.room_photo || QMCONFIG.defAvatar.group_url);
            name = dialog.room_name || user.full_name;
            status = roster[user_id] ? roster[user_id] : null;

            if ($chat.length === 0) {
                if (dialog.type === 3) {
                    html = '<section class="l-workspace l-chat l-chat_private presence-listener j-chatItem" data-dialog="' + dialog_id + '" data-id="' + user_id + '" data-jid="' + jid + '">';
                    // html += '<header class="l-chat-header l-flexbox l-flexbox_flexbetween">';
                } else {
                    html = '<section class="l-workspace l-chat l-chat_group is-group j-chatItem" data-dialog="' + dialog_id + '" data-jid="' + jid + '">';
                    // html += '<header class="l-chat-header l-flexbox l-flexbox_flexbetween groupTitle">';
                }
                // if (tutor == false) {
                //     var profileData = '<div class="wrap-tutor-info">'+$('.list [data-id="'+user_id+'"] .contact').html()+'</div>';
                //     profileData = profileData.replace(/is-hidden/g, '');
                //     profileData = profileData.replace(/<time(.*)time>/, '');
                //     html += profileData;
                // }

                // html += '<div class="chat-title">';
                // html += '<div class="l-flexbox_inline">';
                //
                // if (dialog.type === 3) {
                //     html += '<div class="contact-avatar avatar avatar_chat profileUserAvatar" style="background-image:url(' + icon + ')" data-id="' + user_id + '"></div>';
                //     html += '<h2 class="name name_chat profileUserName" title="' + name + '" data-id="' + user_id + '">' + name + '</h2>';
                //     html = getStatus(status, html);
                // } else {
                //     html += '<div class="contact-avatar avatar avatar_chat" style="background-image:url(' + icon + ')"></div>';
                //     html += '<span class="pencil_active avatar is-hidden"></span>';
                //     html += '<input class="avatar_file avatar is-hidden" type="file" accept="image/*">';
                //     html += '<h2 class="name name_chat" contenteditable="true" title="' + name + '">' + name + '</h2>';
                //     html += '<span class="pencil is-hidden"></span>';
                //     html += '<span class="triangle triangle_down"></span>';
                //     html += '<span class="triangle triangle_up is-hidden"></span>';
                // }
                //
                // html += '</div></div>';
                // html += '<div class="chat-controls">';
                //
                // if (dialog.type === 3) {
                //     html += '<button class="btn_chat btn_chat_videocall videoCall"><img src="images/icon-videocall.svg" alt="videocall"></button>';
                //     html += '<button class="btn_chat btn_chat_audiocall audioCall"><img src="images/icon-audiocall.svg" alt="audiocall"></button>';
                //     html += '<button class="btn_chat btn_chat_add createGroupChat" data-ids="' + dialog.occupants_ids.join() + '" data-private="1"><img src="images/icon-add.svg" alt="add"></button>';
                //     html += '<button class="btn_chat btn_chat_profile userDetails" data-id="' + user_id + '"><img src="images/icon-profile.svg" alt="profile"></button>';
                // } else {
                //     html += '<button class="btn_chat btn_chat_add addToGroupChat" data-ids="' + dialog.occupants_ids.join() + '" data-dialog="' + dialog_id + '"><img src="images/icon-add.svg" alt="add"></button>';
                // }
                //
                // if (dialog.type === 3) {
                //     html += '<button class="btn_chat btn_chat_delete deleteContact"><img src="images/icon-delete.svg" alt="delete"></button>';
                // } else {
                //     html += '<button class="btn_chat btn_chat_delete leaveChat"><img src="images/icon-delete.svg" alt="delete"></button>';
                // }
                //
                // html += '</div></header>';

                // build occupants of room

                // TUTOR NOTIFICATION
                // Add condition for case when student has no payment

                html += '<div class="notification-container">';


                if (tutor == false && !App.identity.isCompanyClient) {
                    if (App.user && (App.user.hasVerifiedPaymentMethod === false)) {
                        // STUDENT NOTIFICATION
                        // Add condition for case when student has no payment
                        var hasVerifiedPaymentMethoUrl = createUrlWithParams(App.endpoints.student.paymentInfo, null, { 'messageVerify': 'true' });
                        html += '<div class="mobile-chat-notification mobile-chat-notification--student">'
                            + '<p>To hire & contact tutors, please verify your account</p>'
                            + '<a href="' + hasVerifiedPaymentMethoUrl + '" class="btn btn-success btn--chat-notification-payment">'
                            + '<img src="images/icon-card.svg" >'
                            + 'Verify </a>'
                            + '</div>';
                    }
                }


                html += '</div>';

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
                textAreaScrollbar();

                // $('.l-workspace[data-id="'+user_id+'"]').find('.wrap-tutor-info').find('.message-list-info > .profile-data').remove();
                // $('.l-workspace[data-id="'+user_id+'"]').find('.wrap-tutor-info').find('.profile-data').insertAfter($('.l-workspace[data-id="'+user_id+'"]').find('.wrap-tutor-info').find('.message-list-info .chat-list_title'));


                if (dialog.type === 3 && (!status || status.subscription === 'none'))
                    $('.l-chat:visible').addClass('is-request');

                self.createDataSpinner(true);
                self.showChatWithNewMessages(dialog_id, unreadCount);
            } else {

                $chat.removeClass('is-hidden')
                     .siblings(':not(.j-popover_const)').addClass('is-hidden');
                // $('.l-chat:visible .scrollbar_message').mCustomScrollbar('destroy');
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
            $(".scrollbar_message").scrollTop(height);
            // $objDom.mCustomScrollbar({
            //     theme: 'minimal-dark',
            //     scrollInertia: 'auto',
            //     mouseWheel: {
            //         scrollAmount: 120,
            //         deltaFactor: 'auto'
            //     },
            //     setTop: height + 'px',
            //     callbacks: {
            //         onTotalScrollBack: function() {
            //             ajaxDownloading($objDom, self);
            //         },
            //         onTotalScroll: function() {
            //
            //             var isBottom = Helpers.isBeginOfChat(),
            //                 $currentDialog = $('.dialog-item.is-selected'),
            //                 dialogId = $currentDialog.data('dialog');
            //
            //             if (isBottom) {
            //                 $('.j-toBottom').hide();
            //                 $currentDialog.find('.unread').text('');
            //                 self.decUnreadCounter(dialogId);
            //             }
            //         },
            //         onScroll: function() {
            //             var isBottom = Helpers.isBeginOfChat();
            //             if (!isBottom) {
            //                 $('.j-toBottom').show();
            //             }
            //         }
            //     },
            //     live: true
            // });

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
            // $chat.mCustomScrollbar('scrollTo', '.j-newMessages');
        } else {
            // $chat.mCustomScrollbar('scrollTo', 'bottom');
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
