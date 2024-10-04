let PublicationDomain = Backbone.Model.extend({
    url: '/publishing',
    defaults: {
        id:       null,
        typeEmailToChanel:   null,
        typeMessageToChanel: null,
    }
})

export { PublicationDomain };
