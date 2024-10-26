let FileDeleteDomain = Backbone.Model.extend({
    defaults: {
        action:  null,
        id:      null,
        fileUrl: null
    },

    url: function() {
        let action  = encodeURIComponent(this.get('action'))
        let id      = encodeURIComponent(this.get('id'))
        let fileUrl = encodeURIComponent(this.get('fileUrl'))


        const actionMap = {
            removeForced: '/detail/remove-file-forced',
            removeSoftly: '/detail/remove-file-softly',
        }

        return actionMap[action] + `?action=${action}&id=${id}&fileUrl=${fileUrl}`
    },
})

export { FileDeleteDomain }
