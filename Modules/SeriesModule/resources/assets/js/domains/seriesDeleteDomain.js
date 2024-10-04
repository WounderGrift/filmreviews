let SeriesDeleteDomain = Backbone.Model.extend({
    defaults: {
        id: null
    },

    url: function() {
        let id = encodeURIComponent(this.get('id'));

        return `/series/delete?id=${id}`;
    }
})

export { SeriesDeleteDomain };
