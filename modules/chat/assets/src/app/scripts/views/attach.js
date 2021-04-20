/*
 * Q-municate chat application
 *
 * Attach View Module
 *
 */
define([
    'jquery',
    'config',
    'quickblox',
    'Helpers',
    'QMHtml',
    //'LocationView',
    'underscore',
    'progressbar'
], function(
    $,
    QMCONFIG,
    QB,
    Helpers,
    QMHtml,
    //Location,
    _,
    ProgressBar
) {

    var self;

    var User,
        Message,
        Attach;

    function AttachView(app) {
        this.app = app;

        User = this.app.models.User;
        Message = this.app.models.Message;
        Attach = this.app.models.Attach;
        self = this;
    }

    AttachView.prototype = {

        changeInput: function(objDom) {
            var file = objDom[0].files[0] || null,
                chat = $('.l-chat:visible .l-chat-content .mCSB_container'),
                id = _.uniqueId(),
                fileSize = file.size,
                fileSizeCrop = fileSize > (1024 * 1024) ? (fileSize / (1024 * 1024)).toFixed(1) : (fileSize / 1024).toFixed(1),
                fileSizeUnit = fileSize > (1024 * 1024) ? 'MB' : 'KB',
                maxSize = QMCONFIG.maxLimitFile * 1024 * 1024,
                errMsg,
                html;

            if (file) {
                if (file.name.length > 100) {
                    errMsg = QMCONFIG.errors.fileName;
                } else if (file.size > maxSize) {
                    errMsg = QMCONFIG.errors.fileSize;
                } else if (file.type.indexOf('video') > -1) {
                    errMsg = QMCONFIG.errors.videoType;
                }

                if (errMsg) {
                    self.pastErrorMessage(errMsg, objDom, chat);
                } else {
                    html = QMHtml.Attach.attach({
                        'fileName': file.name,
                        'fileSizeCrop': fileSizeCrop,
                        'fileSizeUnit': fileSizeUnit,
                        'id': id
                    });
                }

                chat.append(html);
                objDom.val('');
                fixScroll();
                if (file.type.indexOf('image') > -1) {
                    Attach.crop(file, {
                        w: 1000,
                        h: 1000
                    }, function(blob) {
                        self.createProgressBar(id, fileSizeCrop, fileSize, blob);
                    });
                } else {
                    self.createProgressBar(id, fileSizeCrop, fileSize, file);
                }
            }
        },

        pastErrorMessage: function(errMsg, objDom, chat) {
            var html = QMHtml.Attach.error({
                'errMsg': errMsg
            });

            chat.append(html);
            objDom.val('');

            fixScroll();

            return false;
        },

        createProgressBar: function(id, fileSizeCrop, fileSize, file) {
            var progressBar = new ProgressBar('progress_' + id),
                percent = 5,
                isUpload = false,
                part,
                time;

            if (fileSize <= 5 * 1024 * 1024) {
                time = 50;
            } else if (fileSize > 5 * 1024 * 1024) {
                time = 60;
            } else if (fileSize > 6 * 1024 * 1024) {
                time = 70;
            } else if (fileSize > 7 * 1024 * 1024) {
                time = 80;
            } else if (fileSize > 8 * 1024 * 1024) {
                time = 90;
            } else if (fileSize > 9 * 1024 * 1024) {
                time = 100;
            }

            setPercent();

            Helpers.log('File:', file);

            Attach.upload(file, function(blob) {
                Helpers.log('Blob:', blob);

                var chat;
                isUpload = true;
                if ($('#progress_' + id).length > 0) {
                    chat = $('#progress_' + id).parents('.l-chat');
                    setPercent();
                    self.sendMessage(chat, blob, fileSize);
                }
            });

            function setPercent() {
                if (isUpload) {
                    progressBar.setPercent(100);
                    part = fileSizeCrop;
                    $('.attach-part_' + id).text(part);

                    setTimeout(function() {
                        $('.attach-part_' + id).parents('article').remove();
                    }, 50);

                } else {
                    progressBar.setPercent(percent);
                    part = (fileSizeCrop * percent / 100).toFixed(1);
                    $('.attach-part_' + id).text(part);
                    percent += 5;
                    if (percent > 95) return false;
                    setTimeout(setPercent, time);
                }
            }
        },

        cancel: function(objDom) {
            objDom.parents('article').remove();
        },

        sendMessage: function(chat, blob, size, mapCoords) {
            var MessageView = this.app.views.Message,
                jid = chat.data('jid'),
                id = chat.data('id'),
                dialog_id = chat.data('dialog'),
                time = Math.floor(Date.now() / 1000),
                type = chat.is('.is-group') ? 'groupchat' : 'chat',
                dialogItem = type === 'groupchat' ? $('.l-list-wrap section .dialog-item[data-dialog="' + dialog_id + '"]') : $('.l-list-wrap section .dialog-item[data-id="' + id + '"]'),
                copyDialogItem,
                lastMessage,
                message,
                attach;

            if (mapCoords) {
                attach = {
                    'type': 'location',
                    'data': mapCoords.replace(/"/g, '&quot;')
                };
            } else {
                attach = Attach.create(blob, size);
            }

            var getParamsRaw = decodeURIComponent(location.search.substr(1)).split('&');
            var getParams = [];
            for(var i = 0; i < getParamsRaw.length; i ++) {
                var param = getParamsRaw[i].split('=');
                getParams[param[0]] = param[1];
            }

            var paramJobId = getParams['jobId']? getParams['jobId'] : false;

            $.ajax({
                url: createUrlWithParams(App.endpoints.chat.send, [id]),
                method: 'post',
                data: {
                    type: attach['type'],
                    message: blob.uid,
                    jobId: paramJobId,
                },
                success: function (msg) {
                    message = Message.create({
                        'chat_dialog_id': dialog_id,
                        'date_sent': time,
                        'attachment': attach,
                        'sender_id': User.contact.id,
                        'latitude': localStorage['QM.latitude'] || null,
                        'longitude': localStorage['QM.longitude'] || null,
                        '_id': msg.id
                    });

                    Helpers.log(message);
                    if (type === 'chat') {
                        lastMessage = chat.find('article[data-type="message"]').last();

                        message.stack = Message.isStack(true, message, lastMessage);
                        MessageView.addItem(message, true, true);
                    }
                },
                error: function($xhr) {
                    Helpers.sendMessageProhibited($xhr.responseJSON);
                }
            });
        }

    };

    /* Private
    ---------------------------------------------------------------------- */
    function fixScroll() {
        $('.l-chat:visible .scrollbar_message').mCustomScrollbar('scrollTo', 'bottom');
    }

    return AttachView;

});
