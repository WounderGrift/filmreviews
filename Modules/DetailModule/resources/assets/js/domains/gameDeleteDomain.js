let GameDeleteDomain = Backbone.Model.extend({
    url: function() {
        let id = encodeURIComponent(this.get('id'))

        return `/detail/remove-game?id=${id}`
    },

    defaults: {
        id: null,
    }
})

export { GameDeleteDomain }
