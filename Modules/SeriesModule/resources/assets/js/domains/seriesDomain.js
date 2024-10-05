let SeriesDomain = Backbone.Model.extend({
    defaults: {
        action:        null,
        seriesId:      null,
        seriesName:    null,
        description:   null,
        avatarPreview: '',
    },

    url: function() {
        const actionMap = {
            create: '/series/create',
            update: '/series/update',
        }

        let seriesId = encodeURIComponent(this.get('seriesId'))
        return actionMap[this.get('action')] + `?seriesId=${seriesId}`
    }
})

export { SeriesDomain }
