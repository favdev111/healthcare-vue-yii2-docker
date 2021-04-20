/*
 * Q-municate chat application
 *
 * Contact Module
 *
 */
define([
    'config',
    'quickblox'
], function(
    QMCONFIG,
    QB
) {

    function Contact(app) {
        this.app = app;
    }

    Contact.prototype = {

        create: function(qbUser) {
            var full_name = qbUser.full_name || 'Unknown user',
                full_name_default = full_name,
                avatar_url = (qbUser.avatar_url || getAvatar(qbUser)) || QMCONFIG.defAvatar.url,
                avatar_url_default = avatar_url,
                custom_data = qbUser.custom_data && JSON.parse(qbUser.custom_data),
                is_verified = false;

            if (custom_data.client_full_name) {
                full_name_default = custom_data.client_full_name;
                avatar_url_default = custom_data.client_avatar_url;
            }

            if (typeof custom_data.is_verified !== 'undefined') {
                is_verified = !!custom_data.is_verified;
            }

            return {
                id: qbUser.id,
                full_name: full_name,
                full_name_default: full_name_default,
                email: qbUser.email || '',
                phone: qbUser.phone || '',
                blob_id: qbUser.blob_id || null,
                user_tags: qbUser.tag || qbUser.user_tags || null,
                avatar_url: avatar_url,
                avatar_url_default: avatar_url_default,
                status: qbUser.status || getStatus(qbUser),
                user_jid: qbUser.user_jid || QB.chat.helpers.getUserJid(qbUser.id, QMCONFIG.qbAccount.appId),
                custom_data: qbUser.custom_data || null,
                is_verified: is_verified
            };
        }

    };

    /* Private
    ---------------------------------------------------------------------- */
    function getAvatar(contact) {
        var avatar;

        avatar = contact.custom_data && JSON.parse(contact.custom_data).avatar_url;
        if (!avatar) {
            avatar = QMCONFIG.defAvatar.url;
        }

        return avatar;
    }

    function getStatus(contact) {
        return contact.custom_data && JSON.parse(contact.custom_data).status || '';
    }

    return Contact;

});
