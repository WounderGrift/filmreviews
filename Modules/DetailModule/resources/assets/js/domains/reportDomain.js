let ReportDomain = Backbone.Model.extend({
    url: '/detail/send-report-error',
    defaults: {
        text:    null,
        film_id: null
    }
})

export { ReportDomain }
