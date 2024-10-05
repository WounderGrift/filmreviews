let FilesRecyclebinDomain = Backbone.Model.extend({
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
            removeForced: '/detail/remove-torrent-forced',
            removeSoftly: '/detail/remove-torrent-softly',
            clearTrash:   '/recyclebin/cleaning-files',
            download:     '/detail/download',
        }

        if (id) {
            return actionMap[action] + `?id=${id}` + (url ? `&url=${url}` : '')
        }

        if (url) {
            return actionMap[action] + `?url=${url}`
        }
    }
})

export { FilesRecyclebinDomain }
