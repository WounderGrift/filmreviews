let ProfileDomain = Backbone.Model.extend({
    url: function() {
        const actionMap = {
            create:   '/profile/create',
            update:   '/profile/update',
            login:    '/profile/login',
            restore:  '/profile/restore',
            verify:   '/profile/send-email-verify',
            banned:   '/profile/banned',
        };

        return actionMap[this.get('action')];
    },

    defaults: {
        action:    null,
        profileEncodeId: null,
        cid:       null,
        role:      null,
        name:      null,
        email:     null,
        password:  null,
        avatar: '',
        avatar_name: null,
        status:    null,
        about_me:  null,
        get_letter_release: null,
        remember: null,
        timezone: null,
    },
})

export { ProfileDomain };
