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

    QMHtml.VideoChat = {

        onCallTpl: function(params) {
            var htmlTemplate = _.template('<div class="incoming-call l-flexbox l-flexbox_column l-flexbox_flexbetween">' +
                '<div class="incoming-call-info l-flexbox l-flexbox_column">' +
                '<div class="message-avatar avatar info-avatar" style="background-image:url(<%= userAvatar %>)"></div>' +
                '<span class="info-notice"><%= callTypeUС %> Call from <%= userName %></span></div>' +
                '<div class="incoming-call-controls l-flexbox l-flexbox_flexcenter">' +
                '<button class="btn_decline" data-callType="<%= callType %>" data-dialog="<%= dialogId %>"' +
                ' data-id="<%= userId %>">Decline</button>' +
                '<button class="btn_accept" data-callType="<%= callType %>" data-session="<%= sessionId %>"' +
                ' data-dialog="<%= dialogId %>" data-id="<%= userId %>">Accept</button>' +
                '</div></div>')(params);

            return htmlTemplate;
        },

        buildTpl: function(params) {
            var htmlTemplate = _.template('<div class="mediacall l-flexbox">' +
                '<video id="remoteStream" class="mediacall-remote-stream is-hidden"></video>' +
                '<video id="localStream" class="mediacall-local mediacall-local-stream is-hidden"></video>' +
                '<img id="localUser" class="mediacall-local mediacall-local-avatar" src="<%=userAvatar%>" alt="avatar">' +
                '<div id="remoteUser" class="mediacall-remote-user l-flexbox l-flexbox_column">' +
                '<img class="mediacall-remote-avatar" src="<%=contactAvatar%>" alt="avatar">' +
                '<span class="mediacall-remote-name"><%=contactName%></span></div>' +
                '<div class="mediacall-info l-flexbox l-flexbox_column l-flexbox_flexcenter">' +
                '<img class="mediacall-info-logo" src="images/logo-qmunicate-transparent.svg" alt="Q-municate">' +
                '<span class="mediacall-info-duration">connect...</span></div>' +
                '<div class="mediacall-controls l-flexbox l-flexbox_flexcenter">' +
                '<button class="btn_mediacall btn_full-mode" data-id="<%=userId%>" data-dialog="<%=dialogId%>" disabled>' +
                '<div id="fullModeOn" class="btn-icon_mediacall"></div>' +
                '<div id="fullModeOff" class="btn-icon_mediacall"></div></button>' +
                '<button class="btn_mediacall btn_camera_off" data-id="<%=userId%>" data-dialog="<%=dialogId%>">' +
                '<img class="btn-icon_mediacall" src="images/icon-camera-off.svg" alt="camera"></button>' +
                '<button class="btn_mediacall btn_mic_off" data-id="<%=userId%>" data-dialog="<%=dialogId%>">' +
                '<img class="btn-icon_mediacall" src="images/icon-mic-off.svg" alt="mic"></button>' +
                '<button class="btn_mediacall btn_hangup" data-id="<%=userId%>" data-dialog="<%=dialogId%>">' +
                '<img class="btn-icon_mediacall" src="images/icon-hangup.svg" alt="hangup"></button>' +
                '</div></div>')(params);

            return htmlTemplate;
        },

        showError: function() {
            var isBottom = Helpers.isBeginOfChat(),
                $chat = $('.l-chat:visible'),
                $html = $('<article class="message message_service l-flexbox l-flexbox_alignstretch">' +
                    '<span class="message-avatar request-button_pending"></span>' +
                    '<div class="message-container-wrap">' +
                    '<div class="message-container l-flexbox l-flexbox_flexstart l-flexbox_alignstretch">' +
                    '<div class="message-content"><h4 class="message-author message-error">Camera and/or microphone wasn\'t found.' +
                    '</h4></div></div></div></article>');

            $chat.find('.mCSB_container').append($html);

            if (isBottom) {
                $chat.find('.scrollbar_message').mCustomScrollbar('scrollTo', 'bottom');
            }
        },

        noWebRTC: function() {
            var isBottom = Helpers.isBeginOfChat(),
                $chat = $('.l-chat:visible'),
                $html = $('<article class="message message_service l-flexbox l-flexbox_alignstretch">' +
                    '<span class="message-avatar request-button_pending"></span>' +
                    '<div class="message-container-wrap">' +
                    '<div class="message-container l-flexbox l-flexbox_flexstart l-flexbox_alignstretch">' +
                    '<div class="message-content"><h4 class="message-author message-error">' +
                    'Audio and Video calls aren\'t supported by your browser. Please use Google Chrome, Opera or Firefox.' +
                    '</h4></div></div></div></article>');

            $chat.find('.mCSB_container').append($html);

            if (isBottom) {
                $chat.find('.scrollbar_message').mCustomScrollbar('scrollTo', 'bottom');
            }
        }

    };

    QMHtml.User = {

        contactPopover: function(params, roster) {
            var $html = $('<ul class="list-actions list-actions_contacts popover"></ul>'),
                htmlStr = '';

            if (params.dialogType === 3 && roster && roster.subscription !== 'none') {
                htmlStr = '<li class="list-item"><a class="list-actions-action createGroupChat" data-ids="<%=ids%>" data-private="1" href="#">Add people</a></li>';
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
                htmlStr = '<li class="list-item"><a class="list-actions-action writeMessage" data-id="<%=id%>" href="#">Write message</a></li>' +
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
            var html = '<footer class="l-chat-footer">' +
            '<div class="footer_btn j-toBottom btn_to_bottom"></div>' +
            '<form class="l-message j-message" action="#">' +
            '<textarea class="form-input-message textarea" tabindex="0" contenteditable="true" ondragend="return true"></textarea>' +
            '<div class="footer_btn j-send_location btn_sendlocation' + ((localStorage['QM.latitude'] && localStorage['QM.longitude']) ? ' btn_active' : '') + '"' +
            'data-balloon-length="small" data-balloon="Send your location with messages" data-balloon-pos="up"></div>' +
            // '<input class="attachment" type="file" accept="audio/*,video/webm,video/mp4,image/*"></form>' +  (for next release)
            '</form>' +
            '<label class="l-attach-file-label"><input class="attachment" type="file"><span class="attach-btn j-btn_input_attach">' +
            '<svg width="23px" height="22px" viewBox="290 651 23 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">' +
            '<path d="M300.595628,669.889246 L304.292644,666.014301 L307.975534,662.245816 C308.861551,661.327535 309.3389,660.358799 309.407093,659.339103 C309.485027,658.35069 309.237098,657.4647 308.662331,656.683151 C308.086591,655.900594 307.336959,655.424803 306.411487,655.252247 C306.187425,655.211883 305.953622,655.192206 305.710077,655.192206 C305.184019,655.192206 304.660397,655.326416 304.139698,655.593323 C303.618024,655.86023 303.209355,656.133192 302.911743,656.41019 C302.614618,656.688197 302.213255,657.089314 301.706194,657.613542 L296.620971,662.88155 C296.426135,663.083874 296.211815,663.289731 295.978012,663.501642 C295.607823,663.864918 295.335053,664.137879 295.1597,664.319517 C294.984835,664.500651 294.780257,664.752926 294.545966,665.076343 C294.312163,665.399255 294.159217,665.716617 294.085666,666.029942 C294.012602,666.342259 293.999938,666.675766 294.049621,667.028447 C294.137297,667.66418 294.399839,668.149053 294.83822,668.482056 C295.276602,668.815059 295.763692,668.98156 296.299491,668.98156 C297.010643,668.98156 297.619506,668.719194 298.12608,668.193958 L306.119233,659.91429 C306.470425,659.552023 306.567356,659.173611 306.411487,658.779053 C306.265847,658.416786 306.007202,658.234138 305.637013,658.234138 C305.374472,658.234138 305.130439,658.35069 304.906378,658.582278 C304.721283,658.743734 304.531318,658.935968 304.336482,659.157465 L298.827977,664.803381 L297.819213,663.789235 L297.921502,663.683784 L303.591721,657.689224 C303.922455,657.34613 304.250754,657.089314 304.577592,656.917767 C304.903942,656.74622 305.291179,656.660447 305.739789,656.660447 C305.827466,656.660447 305.968235,656.670538 306.163071,656.690215 C307.040321,656.862267 307.610216,657.386999 307.872758,658.264916 C308.019372,658.738689 308.033498,659.223561 307.916596,659.717516 C307.799694,660.211975 307.571249,660.635797 307.230286,660.989487 L301.472391,666.953269 C301.306293,667.124816 301.029139,667.432592 300.639466,667.8771 C300.260023,668.310509 299.969717,668.633925 299.77001,668.845332 C299.570303,669.057243 299.314581,669.286813 299.00333,669.533538 C298.691105,669.780768 298.374496,669.985615 298.053017,670.147576 C297.507963,670.429115 296.927838,670.570893 296.314104,670.570893 C295.632178,670.570893 295.003831,670.409437 294.429065,670.086021 C293.854785,669.763109 293.396433,669.314563 293.05547,668.739376 C292.58835,667.951774 292.417869,667.079406 292.544025,666.120761 C292.671156,665.162116 293.065212,664.340204 293.728142,663.653007 L301.209362,655.903621 C301.90103,655.18716 302.441214,654.707333 302.831373,654.465654 C303.708623,653.911153 304.667704,653.633146 305.710077,653.633146 C306.519134,653.633146 307.259024,653.799648 307.931696,654.132651 C308.700812,654.495422 309.334029,655.015613 309.830861,655.69171 C310.32818,656.367808 310.654531,657.14431 310.809913,658.022227 C310.975523,658.960691 310.921943,659.884017 310.64966,660.792712 C310.376402,661.700902 309.913179,662.493551 309.260965,663.169143 L301.706194,670.904401 L300.697917,669.995706 L300.595628,669.889246" id="Fill-194" stroke="none" fill="#868686" fill-rule="evenodd" transform="translate(301.703785, 662.268773) rotate(-15.000000) translate(-301.703785, -662.268773) "></path>' +
            '</svg></span></label><div class="j-typing l-typing"></div><div class="l-input-menu">' +
            '<div class="footer_btn l-input-buttons btn_input_smile j-btn_input_smile" data-balloon="Add smiles" data-balloon-pos="up"></div>' +
            '<div class="footer_btn l-input-buttons btn_input_location j-btn_input_location" data-balloon="Send location" data-balloon-pos="up"></div>' +
            // '<div class="footer_btn l-input-buttons btn_input_attach j-btn_input_attach"><span>Attach File<span></div>' +
            '<button class="footer_btn l-input-buttons btn_input_send j-btn_input_send">' + 
            '<svg width="20px" height="17px" viewBox="332 654 20 17" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><polygon id="Triangle-2" stroke="none" fill="#00A4F1" fill-rule="evenodd" transform="translate(342.000000, 662.500000) rotate(90.000000) translate(-342.000000, -662.500000) " points="342 652.5 350.5 672.5 343.465517 672.5 342.293103 661.824138 341.12069 672.5 333.5 672.5"></polygon></svg>' +
            '</button></div></footer>';

            return html;
        }

    };

    QMHtml.Attach = {

        error: function(params) {
            var htmlTemplate = _.template('<article class="message message_service l-flexbox l-flexbox_alignstretch">'+
                '<span class="message-avatar request-button_pending"></span>'+
                '<div class="message-container-wrap">'+
                '<div class="message-container l-flexbox l-flexbox_flexstart l-flexbox_alignstretch">'+
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
                '<div class="message-container l-flexbox l-flexbox_flexstart l-flexbox_alignstretch">' +
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
