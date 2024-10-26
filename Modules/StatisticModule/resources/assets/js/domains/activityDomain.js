let ActivityDomain = Backbone.Model.extend({
    defaults: {
        text:   null,
        downloads:    0,
        commentaries: 0,
        likesTofilm:  0,
        likesToComments: 0,
        wishlist:     0,
        newsletterUpdate: 0,
        data:   false
    },

    url: '/chart/activity/range',
})

export { ActivityDomain }
