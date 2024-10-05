let GameRecyclebinDomain = Backbone.Model.extend({
    defaults: {
        action: null,
        id:     null
    },

    url: function() {
        let action = encodeURIComponent(this.get('action'))
        let id     = this.get('id') ? encodeURIComponent(this.get('id')) : ''

        const actionMap = {
            remove:     '/recyclebin/remove-games',
            restore:    '/recyclebin/restore-games',
            clearTrash: '/recyclebin/cleaning-games',
        }

        return actionMap[action] + (id ? `?id=${id}` : '')
    }
})

export { GameRecyclebinDomain }
