import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {ScreenshotsRecyclebinDomain as ScreenshotsRecyclebinModel} from "./domains/screenshotsRecyclebinDomain.js"

let SpoilerHeadView = Backbone.View.extend({
    setup: function () {
        this.spoiler = $('.spoiler')
        $(window).on('resize', this.resizeSpoiler.bind(this))
        $(document).on('click', '.spoiler-header', this.openSpoiler.bind(this))
    },

    openSpoiler: function (event) {
        $(document).on('click', '.options .option', function(event) {
            return false
        })

        if ($(event.target).is('input[type="text"]'))
            return false

        if (!$(event.target).hasClass('download')) {
            let spoiler = $(event.currentTarget).closest('.spoiler')
            spoiler.toggleClass('open')

            let spoilerContent = spoiler.find('.spoiler-content')
            let maxHeight = spoiler.hasClass('open')
                ? spoilerContent.prop('scrollHeight') + 'px' : '0'
            spoilerContent.css('max-height', maxHeight)
        }
    },

    resizeSpoiler: function () {
        this.spoiler.each(function () {
            let thisSpoiler = $(this)
            let spoilerContent = thisSpoiler.find('.spoiler-content')
            let maxHeight   = thisSpoiler.hasClass('open')
                ? spoilerContent.prop('scrollHeight') + 'px' : '0'
            spoilerContent.css('max-height', maxHeight)
        })
    }
})

let RecyclebinScreenshotsView = Backbone.View.extend({
    setup: function(options) {
        this.model = options.model
        this.isFormSubmitting = false
        this.loader = $('#main-loader')

        $('[data-fancybox="gallery"]').fancybox({})

        $('.gallery.removed').on('click', '.photo-container .restore-screen',
            this.removeSoftlyScreen.bind(this))
        $(document).on('click', '.remove-screen-force', this.removeForcedScreen.bind(this))
        $('#empty-trash').on('click', this.clearRecycleBin.bind(this))
    },

    removeSoftlyScreen: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        let photoContainer = $(event.currentTarget).closest(".photo-container")
        this.model.set('action', 'removeSoftly')
        this.model.set('id', photoContainer.data('id'))

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    this.loader.removeClass('show')
                    photoContainer.remove()
                }
                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_trashed_screenshots'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    },

    removeForcedScreen: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        let photoContainer = $(event.currentTarget).closest(".photo-container")
        this.model.set('action', 'removeForced')
        this.model.set('id', photoContainer.data('id'))
        this.model.set('url', photoContainer.find('img').attr('src'))

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    photoContainer.remove()
                }

                this.loader.removeClass('show')
                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_trashed_screenshots'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    },

    clearRecycleBin: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.set('action', 'clearTrash')
        this.model.set('id', 1)

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    this.loader.removeClass('show')
                    location.reload()
                }
                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_trashed_screenshots'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    }
})

let screenshotsRecyclebinModel = new ScreenshotsRecyclebinModel()
new SpoilerHeadView().setup()

new RecyclebinScreenshotsView().setup({model: screenshotsRecyclebinModel})
