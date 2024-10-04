import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {SeriesDeleteDomain as SeriesDeleteModel} from "./domains/seriesDeleteDomain.js";

let FilterView = Backbone.View.extend({
    el: '#filter',

    events: {
        'submit': 'filterTable'
    },

    setup: function() {
        let path = window.location.pathname
        let regex = /\/all\/([^\/]*)/
        let matches = path.match(regex)

        if (matches && matches[1]) {
            let searchValue = decodeURIComponent(matches[1])
            this.$('input').val(searchValue)
        }
    },

    filterTable: function (event) {
        event.preventDefault()
        let searchValue = this.$('input').val().trim()
        window.location.href = `/series/all/${searchValue}`
    }
})

let RemoveSeries = Backbone.View.extend({
    el: '.remove-series',

    events: {
        'click': "removeSeries"
    },

    setup:function (options) {
        this.model  = options.model
        this.loader = $('#main-loader')

        this.isFormSubmitting = false
    },

    removeSeries: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.set('id', $(event.currentTarget).data('code'))
        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success)
                    location.reload()

                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_series'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    }
})

new FilterView().setup()

let seriesDeleteModel = new SeriesDeleteModel()
new RemoveSeries().setup({model: seriesDeleteModel})
