let CommentDeleteDomain = Backbone.Model.extend({
    defaults: {
        id:   null,
        hard: false
    },

    url: function() {
        let id   = encodeURIComponent(this.get('id'));
        let hard = encodeURIComponent(this.get('hard'));

        return `/detail/remove-comment?id=${id}&hard=${hard}`;
    }
})

export { CommentDeleteDomain };
