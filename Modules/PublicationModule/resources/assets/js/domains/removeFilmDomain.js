let RemoveFilmDomain = Backbone.Model.extend({
    url: function() {
        let id = encodeURIComponent(this.get('id'))

        return `/publish/remove?id=${id}`
    },

    defaults: {
        id: null,
    }
})

export { RemoveFilmDomain }
