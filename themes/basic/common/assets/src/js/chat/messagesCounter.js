jQuery(function () {
    setTimeout(function () {
        var chatUserId = localStorage.getItem('chat_user_id');
        if (chatUserId == App.chat.user.chatId) {
            var token = localStorage.getItem('chat_token');
        }
        var params = {login: App.chat.user.login, password: App.chat.user.password};
        if (token != 'undefined' && token) {
            QB.init(token, App.chat.account.appId, null, config(params));
            getCountUnreadMessages(params);
            setInterval(function () {
                getCountUnreadMessages(params);
            }, 20000);
        } else {
            QB.init(App.chat.account.appId, App.chat.account.authKey, App.chat.account.authSecret, config(params));
            QB.createSession(params, function (err, result) {
                if (!err && result) {
                    localStorage.setItem('chat_token', result.token);
                    localStorage.setItem('chat_user_id', result.user_id);
                    getCountUnreadMessages(params);
                    setInterval(function () {
                        getCountUnreadMessages(params);
                    }, 20000);
                }
            });
        }
    } , 2000);
});

function config(params) {
    return {
        endpoints: {
            api: App.chat.account.endpointApi,
            chat: App.chat.account.endpointChat
        },
        on: {
            sessionExpired: function (next, retry) {
                QB.createSession(params, function (err, result) {
                    if (!err && result) {
                        localStorage.setItem('chat_token', result.token);
                        localStorage.setItem('chat_user_id', result.user_id);
                        retry();
                    } else {
                        next();
                    }
                });
            }
        }
    }
}

function getCountUnreadMessages(params) {
    QB.chat.dialog.list(params, function(err, res) {
        if (err) {
            QB.init(App.chat.account.appId, App.chat.account.authKey, App.chat.account.authSecret, config(params));
            QB.createSession(params, function (err, result) {
                if (!err && result) {
                    localStorage.setItem('chat_token', result.token);
                    localStorage.setItem('chat_user_id', result.user_id);
                }
            });
            console.log(err);
        } else {
            var count = 0;
            $.ajax({
                url: App.endpoints.blockedUsers,
                success: function (users) {
                    if (!users) {
                        users = [];
                    }
                    for (var i = 0; i < users.length; i++) {
                        usersBlocked.push(parseInt(users[i]));
                    }
                    if ((typeof res === 'object') && res.items) {
                        $.each(res.items, function (index, item) {
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
                            $('.badge-count-message').text(count);
                        }
                    }
                    if (count) {
                        $('.badge-count-message').text(count);
                    }
                    getTotalUnreadItems();
                }
            });
        }
    });
}
