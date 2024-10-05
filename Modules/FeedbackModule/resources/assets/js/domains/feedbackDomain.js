let FeedbackDomain = Backbone.Model.extend({
    url: '/feedback/send-feedback',
    defaults: {
        email:  null,
        letter: null,
    }
})

export {FeedbackDomain}
