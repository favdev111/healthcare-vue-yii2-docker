/**
 *
 * htmlQM Module
 *
 */
define([
    'jquery',
    'underscore',
    'MainModule',
    'Helpers'
], function(
    $,
    _,
    QM,
    Helpers
) {
    var QMHtml = {};

    QMHtml.User = {

        contactPopover: function(params, roster) {
            var $html = $('<ul class="list-actions list-actions_contacts popover"></ul>'),
                htmlStr = '';

            if (params.dialogType === 3 && roster && roster.subscription !== 'none') {
                htmlStr = '<li class="list-item"><a class="videoCall list-actions-action writeMessage" data-id="<%=ids%>" href="#">Video call</a></li>' +
                    '<li class="list-item"><a class="audioCall list-actions-action writeMessage" data-id="<%=ids%>" href="#">Audio call</a></li>' +
                    '<li class="list-item"><a class="list-actions-action createGroupChat" data-ids="<%=ids%>" data-private="1" href="#">Add people</a></li>';
            } else if (params.dialogType !== 3) {
                htmlStr = '<li class="list-item"><a class="list-actions-action addToGroupChat" data-group="true" data-ids="<%=occupantsIds%>" ' +
                    'data-dialog="<%=dialogId%>" href="#">Add people</a></li>';
            }

            if (params.dialogType === 3) {
                htmlStr += '<li class="list-item"><a class="list-actions-action userDetails" data-id="<%=ids%>" href="#">Profile</a></li>' +
                    '<li class="list-item"><a class="deleteContact list-actions-action" href="#">Delete contact</a></li>';
            } else {
                htmlStr += '<li class="list-item"><a class="leaveChat list-actions-action" data-group="true" href="#">Leave chat</a></li>';
            }

            return $html.append(_.template(htmlStr)(params));
        },

        occupantPopover: function(params, roster) {
            var $html = $('<ul class="list-actions list-actions_occupants popover"></ul>'),
                htmlStr = '';

            if (!roster || (roster.subscription === 'none' && !roster.ask)) {
                htmlStr = '<li class="list-item j-listItem" data-jid="<%=jid%>">' +
                    '<a class="list-actions-action requestAction j-requestAction" data-id="<%=id%>" href="#">Send request</a></li>';
            } else if (roster.ask === 'subscribe' && !roster.status) {
                htmlStr = '<li class="list-item"><a class="list-actions-action userDetails" data-id="<%=id%>" href="#">Profile</a></li>' +
                    '<li class="list-item"><a class="deleteContact list-actions-action" data-id="<%=id%>" href="#">Delete contact</a></li>';
            } else {
                htmlStr = '<li class="list-item"><a class="videoCall list-actions-action writeMessage" data-id="<%=id%>" href="#">Video call</a></li>' +
                    '<li class="list-item"><a class="audioCall list-actions-action writeMessage" data-id="<%=id%>" href="#">Audio call</a></li>' +
                    '<li class="list-item"><a class="list-actions-action writeMessage" data-id="<%=id%>" href="#">Write message</a></li>' +
                    '<li class="list-item"><a class="list-actions-action userDetails" data-id="<%=id%>" href="#">Profile</a></li>';
            }

            return $html.append(_.template(htmlStr)(params));
        },

        profilePopover: function() {
            var html = $('<ul class="list-actions list-actions_profile popover">' +
                '<li class="list-item"><a id="userProfile" class="list-actions-action" href="#">Profile</a></li>' +
                '<li class="list-item"><a id="userSettings" class="list-actions-action" href="#">Settings</a></li>' +
                '<li class="list-item"><a id="logout" class="list-actions-action" href="#">Log Out</a></li></ul>');

            return html;
        }

    };

    QMHtml.Messages = {

        setMap: function(params) {
            var htmlTemplate = _.template('<div class="popover_map"><a class="open_map" href="<%=mapLink%>" target="_blank">' +
                '<image class="static_map" src="<%=imgUrl%>"></a><div class="coner"><i class="icon_coner">' +
                '</i></div></div>')(params);

            $('article#' + params.id).find('.message-geo')
                .addClass('with-geo')
                .append(htmlTemplate);
        }

    };

    QMHtml.Dialogs = {

        setTextarea: function() {

            var addLink = (window.App.identity.isTutor) ? '<button class="btn_add_student" id="add-student-from-chat-link">Add student</button>' +
            '<div class="wrap-tooltip">' +
            '<div class="tooltip-button"data-show-tooltip><img src="images/info-icon.svg"></div>' +
            '<div class="help-block help-block--chat"><div class="help-block__wrapper help-block__wrapper--chat">' +
            '<span class="help-block__info-icon"><img src="images/modal-info-icon.svg"></span>' + 
            '<span class="help-block__text">In order to bill students after lessons, you must first add them to your client list.</span>' +
            '<button class="help-block__btn-close" type="button" data-hide-tooltip><img src="images/modal-close-icon.svg"></button>' +
            '</div></div>' : '';

            var html = '<footer class="l-chat-footer">' +
            '<div class="footer_btn j-toBottom btn_to_bottom"></div>' +
            '<form class="l-message j-message" action="#">' +
            '<textarea class="form-input-message textarea" tabindex="0" contenteditable="true" ondragend="return true"></textarea>' +
            '<div class="footer_btn j-send_location btn_sendlocation' + ((localStorage['QM.latitude'] && localStorage['QM.longitude']) ? ' btn_active' : '') + '"' +
            'data-balloon-length="small" data-balloon="Send your location with messages" data-balloon-pos="up"></div>' +
            // '<input class="attachment" type="file" accept="audio/*,video/webm,video/mp4,image/*"></form>' +  (for next release)
            '<label class="l-attach-file-label"><input class="attachment" type="file"><span class="attach-btn j-btn_input_attach"><img src="images/icon-attach.svg" alt=""></span></label></form>' +
            '<div class="l-input-menu">' +
            '<div class="footer_btn l-input-buttons btn_input_smile j-btn_input_smile" data-balloon="Add smiles" data-balloon-pos="up"></div>' +
            '<div class="footer_btn l-input-buttons btn_input_location j-btn_input_location" data-balloon="Send location" data-balloon-pos="up"></div>' +
            // '<div class="footer_btn l-input-buttons btn_input_attach j-btn_input_attach"><span>Attach File<span></div>' +
            '<button class="footer_btn l-input-buttons btn_input_send j-btn_input_send">Send</button></div>'+ addLink +'</footer>';

            return html;
        }

    };

    QMHtml.Attach = {

        error: function(params) {
            var htmlTemplate = _.template('<article class="message message_service l-flexbox l-flexbox_alignstretch">'+
                '<span class="message-avatar request-button_pending"></span>'+
                '<div class="message-container-wrap">'+
                '<div class="message-container l-flexbox l-flexbox_flexbetween l-flexbox_alignstretch">'+
                '<div class="message-content">'+
                '<h4 class="message-author message-error"><%= errMsg %></h4>'+
                '</div></div></div></article>')(params);

            return htmlTemplate;
        },

        attach: function(params) {
            var htmlTemplate = _.template(
                '<article class="message message_service message_attach l-flexbox l-flexbox_alignstretch">' +
                '<span class="message-avatar request-button_attach">' +
                '<img src="images/icon-attach.svg" alt="attach"></span>' +
                '<div class="message-container-wrap">' +
                '<div class="message-container l-flexbox l-flexbox_flexbetween l-flexbox_alignstretch">' +
                '<div class="message-content">' +
                '<h4 class="message-author"><%= fileName %><div class="attach-upload">' +
                '<div id="progress_<%= id %>"></div>' +
                '<span class="attach-size"><span class="attach-part attach-part_<%= id %>">' +
                '</span> of <%= fileSizeCrop %> <%= fileSizeUnit %></span>' +
                '</div></h4></div>' +
                '<time class="message-time"><a class="attach-cancel" href="#">Cancel</a></time>' +
                '</div></div></article>')(params);

            return htmlTemplate;
        }

    };

    return QMHtml;
});
