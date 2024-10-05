let DetailDomain = Backbone.Model.extend({
    defaults: {
        gameId:     null,
        gameName:   null,
        series:     null,
        categories: null,
        release:    false,
        checkboxes: {
            isPublic:  false,
            isSponsor: false,
            isSoft:    false,
            isWaiting: false,
            isWeak:    false,
        },
        avatarGrid:     '',
        avatarPreview:  '',
        previewTrailer: '',
        getAvatarPreviewFromScreen: false,
        dateRelease: null,
        torrentsNew: null,
        torrentsOld: null,
        screenshotsNew: {},

        summaryObject: null,
        description: $('.text-show').html(),
        requireObject: null,

        trailer:  $('#videoContainer').data('trailer'),
    },

    url: function() {
        let action = encodeURIComponent(this.get('action'))

        const actionMap = {
            release: '/detail/release',
            create:  '/detail/create',
        }

        return actionMap[action]
    },
})

export { DetailDomain }
