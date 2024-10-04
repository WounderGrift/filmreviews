let ReportDomain = Backbone.Model.extend({
    url: '/detail/send-report-error',
    defaults: {
        text:    null,
        game_id: null
    }
})

export { ReportDomain };
