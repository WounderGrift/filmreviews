let CommentDomain = Backbone.Model.extend({
    url: '/detail/send-comment',
    defaults: {
        whom_id: null,
        quote:   null,
        film_id: null,
        comment: null
    }
})

export { CommentDomain }
