let ScreenshotsRecyclebinDomain = Backbone.Model.extend({
    defaults: {
        action: null,
        id:     null,
        url:    null
    },

    url: function() {
        let action = encodeURIComponent(this.get('action'))
        let id  = this.get('id') ? encodeURIComponent(this.get('id')) : ''
        let url = this.get('url') ? encodeURIComponent(this.get('url')) : ''

        const actionMap = {
            removeForced: '/detail/remove-screen-forced',
            removeSoftly: '/detail/remove-screen-softly',
            clearTrash:   '/recyclebin/cleaning-screen',
        }

        if (id) {
            return actionMap[action] + `?id=${id}` + (url ? `&url=${url}` : '')
        }

        if (url) {
            return actionMap[action] + `?url=${url}`
        }
    }
})

export { ScreenshotsRecyclebinDomain }
