let FilmDeleteDomain = Backbone.Model.extend({
    url: function() {
        let id = encodeURIComponent(this.get('id'))

        return `/detail/remove-film?id=${id}`
    },

    defaults: {
        id: null,
    }
})

export { FilmDeleteDomain }
