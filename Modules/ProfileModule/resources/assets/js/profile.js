import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {ProfileDomain as ProfileModel} from "./domains/profileDomain.js"
import {ProfileChartDomain as ChartModel} from "./domains/chartDomain.js"

let ProfileChart = Backbone.View.extend({
    el: '#chartContainer',

    setup: function(options) {
        this.model = options.model
        let optionsChart = {
            animationEnabled: true,
            data: [
                {
                    type: "column",
                    dataPoints: [
                        {label: "Поддержка", y: 0},
                        {label: "Загрузок", y: 0},
                        {label: "Комменты", y: 0},
                        {label: "Лайки на игры", y: 0},
                        {label: "Лайки на комменты", y: 0},
                        {label: "Желаемые", y: 0},
                        {label: "Подписок", y: 0},
                    ]
                }
            ]
        }

        $('#chartContainer').CanvasJSChart(optionsChart)
        this.$('.canvasjs-chart-credit').hide()

        this.listenTo(this.model, 'showData', this.showData)
        this.listenTo(this.model, 'showError', this.showError)
        this.getData()
    },

    getData: function() {
        this.model.set('code', this.$el.data('code'))

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                this.model.trigger('showData', response.dataChart)
            },
            error: (model, xhr) => {
                this.model.trigger('showError', xhr.responseJSON.message)
            }
        })
    },

    showData: function(dataChart) {
        let optionsChart = {
            animationEnabled: true,
            data: [
                {
                    type: "column",
                    dataPoints: [
                        {label: "Поддержка", y: dataChart?.support ?? 0},
                        {label: "Загрузок", y: dataChart?.downloads ?? 0},
                        {label: "Комменты", y: dataChart?.comments ?? 0},
                        {label: "Лайки на игры", y: dataChart?.likesTofilms ?? 0},
                        {label: "Лайки на комменты", y: dataChart?.likesToComments ?? 0},
                        {label: "Желаемые", y: dataChart?.wishlist ?? 0},
                        {label: "Подписок", y: dataChart?.newsletters ?? 0},
                    ]
                }
            ]
        }

        $('#chartContainer').CanvasJSChart(optionsChart)
        this.$('.canvasjs-chart-credit').hide()
    },

    showError: function(errorMessage) {
        if (errorMessage)
            new AlertView().errorWindowShow($('.error'), errorMessage)
        $('#main-loader').removeClass('show')
    }
})

let VerifyQuery = Backbone.View.extend({
    el: '#verify-email',

    events: {
        'submit': 'submitForm'
    },

    setup: function(options) {
        this.model  = options.model
        this.loader = $('#main-loader')
        this.window = $('.error-verify h3')
        this.submitButton = this.$('[type="submit"]')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    submitForm: function(event) {
        event.preventDefault()

        if (this.submitButton.prop('disabled')) {
            return
        }

        this.submitButton.prop('disabled', true)
        this.loader.addClass('show')

        this.model.set('action', 'verify')
        this.model.set('name', $('.info_title').data('name'))
        this.model.set('email', $('.news_content').data('email'))

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.message) {
            new AlertView().startTimer(this.submitButton, this.window, response.message)
            this.loader.removeClass('show')
        }
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message)
            new AlertView().errorWindowShow(this.window, error.responseJSON.message)

        this.loader.removeClass('show')
        this.submitButton.prop('disabled', false)
    }
})

let ProfileBanQuery = Backbone.View.extend({
    el: '#ban-button',

    events: {
        'click': 'banUser'
    },

    setup: function(options) {
        this.model = options.model

        this.listenTo(this.model, 'request', this.showLoader)
        this.listenTo(this.model, 'sync', this.redirectToProfile)
        this.listenTo(this.model, 'error', this.showError)
    },

    banUser: function(event) {
        event.preventDefault()

        this.model.set('action', 'banned')
        this.model.set('profileEncodeId', this.$el.data('code'))

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    },

    showLoader: function() {
        $('#main-loader').addClass('show')
        this.$el.prop('disabled', true)
    },

    redirectToProfile: function(model, response) {
        if (response.redirect_url) {
            window.location.href = response.redirect_url
        }
    },

    showError: function(model, error) {
        if (error?.responseJSON?.message) {
            new AlertView().errorWindowShow($('.error'), error.responseJSON.message)
            $('html, body').scrollTop(0)
            $('#main-loader').removeClass('show')
        }

        this.$el.prop('disabled', false)
    }
})

let SkeletonLoader = Backbone.View.extend({
    el: window,

    setup: function() {
        $(window).on('load', this.onLoad, () => this.onLoad())
    },

    onLoad: function() {
        $('.profile-skeleton-avatar').remove()
        $('.profile-avatar').show()
    }
})

let chartModel = new ChartModel()
new ProfileChart().setup({model: chartModel})

let profileModel = new ProfileModel()
new VerifyQuery().setup({model: profileModel})
new ProfileBanQuery().setup({model: profileModel})

new SkeletonLoader().setup()
