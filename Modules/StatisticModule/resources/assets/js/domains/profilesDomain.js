let ProfilesDomain = Backbone.Model.extend({
    defaults: {
        startDate: null,
        allUsers:  null
    },

    url: '/chart/profiles/range',
})

export { ProfilesDomain }
