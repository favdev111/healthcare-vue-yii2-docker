/* Configuration your application */
define(function() {

    var QMCONFIG = {

        debug: false,

        notification: {
            timeout: 7
        },

        defAvatar: {
            url: 'images/ava-single.svg',
            url_png: 'images/ava-single.png',
            group_url: 'images/ava-group.svg',
            group_url_png: 'images/ava-group.png',
            caption: 'Choose user picture'
        },

        maxLimitFile: 10,

        errors: {
            session: "The QB application credentials you entered are incorrect",
            invalidName: "Name mustn't contain '<', '>' and ';' characters",
            shortName: "Name must be more than 2 characters",
            bigName: "Name mustn't be more than 50 characters",
            invalidEmail: "Please enter a valid Email address",
            invalidPhone: "Phone mustn't contain letters",
            oldPass: "Old password is incorrect",
            invalidPass: "Password mustn't contain non-Latin characters and spaces",
            shortPass: "Password must be more than 7 characters",
            bigPass: "Password mustn't be more than 40 characters",
            avatarType: "Avatar must be image",
            fileName: "File name mustn't be more than 100 characters",
            fileSize: "File mustn't be more than 10 MB",
            emailExists: "The email has already been taken",
            unauthorized: "The email or password is incorrect",
            notFoundEmail: "The email you entered wasn't found",
            videoType: "We are unable to upload video",
        },

        QBconf: {
            chatProtocol: {
                active: 2
            },
            debug: {
                mode: 0,
                file: null
            },
            webrtc: {
                answerTimeInterval: 45,
                statsReportTimeInterval: 5
            }
        }

    };

    return QMCONFIG;

});
