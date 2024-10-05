import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {PublicationDomain as PublicationModel} from "../../assets/js/domains/publicationDomain.js"
import {RemoveGameDomain as RemoveGameModel} from "../../assets/js/domains/removeGameDomain.js"

let Publish = Backbone.View.extend({
    el: '#publish-game',

    events: {
        'click': 'onClickPublish',
    },

    setup: function (options) {
        this.model  = options.model
        this.model.set('id', $(this.el).data('game-id'))
        this.loader = $('#main-loader')

        this.isSubmit = false
    },

    onClickPublish: function(event) {
        event.preventDefault()

        if (this.isSubmit)
            return
        this.isSubmit = true

        this.model.set('typeEmailToChanel', $('input[name="email_type"]:checked').val())
        this.model.set('typeMessageToChanel', $('input[name="message_type"]:checked').val())

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.redirect_url)
                    window.location.href = response.redirect_url
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_public'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isSubmit = false
            }
        })
    }
})

let RemoveGame = Backbone.View.extend({
    el: '#delete-game',

    events: {
        'click': 'onClickRemove',
    },

    setup: function (options) {
        this.model  = options.model
        this.model.set('id', $(this.el).data('game-id'))
        this.loader = $('#main-loader')

        this.isSubmit = false
    },

    onClickRemove: function(event) {
        event.preventDefault()

        if (this.isSubmit)
            return
        this.isSubmit = true
        this.loader.addClass('show')

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.redirect_url)
                    window.location.href = response.redirect_url
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_public'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isSubmit = false
            }
        })
    }
})

let publicationModel = new PublicationModel()
new Publish().setup({model: publicationModel})

let removeGameModel = new RemoveGameModel()
new RemoveGame().setup({model: removeGameModel})
