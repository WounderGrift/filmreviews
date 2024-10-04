let TorrentDeleteDomain = Backbone.Model.extend({
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
            removeForced: '/detail/remove-torrent-forced',
            removeSoftly: '/detail/remove-torrent-softly',
        };

        return actionMap[action] + `?action=${action}&id=${id}&fileUrl=${fileUrl}`
    },
});

export { TorrentDeleteDomain };
