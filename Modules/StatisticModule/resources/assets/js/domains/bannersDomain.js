let BannersDomain = Backbone.Model.extend({
    defaults: {
        startDate:  null
    },

    url: '/chart/banners/range',
})

export { BannersDomain };
