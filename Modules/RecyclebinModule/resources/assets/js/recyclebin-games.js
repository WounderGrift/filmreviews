import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {GameRecyclebinDomain as GameRecyclebinModel} from "./domains/gameRecyclebinDomain.js"

let RecyclebinGamesView = Backbone.View.extend({
    setup: function(options) {
        this.model  = options.model
        this.isFormSubmitting = false
        this.loader = $('#main-loader')

        $('.remove-game').on('click', this.removeGame.bind(this))
        $('.reset-game').on('click', this.resetGame.bind(this))
        $('#empty-trash').on('click', this.clearRecycleBin.bind(this))
    },

    removeGame: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.set('action', 'remove')
        this.model.set('id', $(event.currentTarget).data('game-id'))

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
                    new AlertView().errorWindowShow($('.error_trashed_games'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    },

    resetGame: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.set('action', 'restore')
        this.model.set('id', $(event.currentTarget).data('game-id'))

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
                    new AlertView().errorWindowShow($('.error_trashed_games'), error.responseJSON.message)
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
                console.log(response)
                if (response.success) {
                    this.loader.removeClass('show')
                    location.reload()
                }

                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_trashed_games'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    }
})

let gameRecyclebinModel = new GameRecyclebinModel()
new RecyclebinGamesView().setup({model: gameRecyclebinModel})
