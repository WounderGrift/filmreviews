import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {FeedbackDomain as FeedbackModel} from "./domains/feedbackDomain.js"

let SendFeedback = Backbone.View.extend({
    el: '#send',

    events: {
        'click': 'release'
    },

    setup: function(options) {
        this.isFormSubmitting = false

        this.model  = options.model
        this.window = $('.error_feedback')
        this.loader = $('#main-loader')

        this.counterLetter()
        this.listenTo(this.model, 'sync', this.onSubmitSuccess.bind(this))
        this.listenTo(this.model, 'error', this.onSubmitError.bind(this))
    },

    release: function() {
        this.loader.addClass('show')

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        $('#send').prop('disabled', this.isFormSubmitting)

        this.model.set('email', $('#email').val())
        this.model.set('letter', $('#letter-textarea').val())

        this.model.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        })
    },

    counterLetter: function() {
        $('#letter-textarea').on('input', function() {
            $('h5').text('300 / ' + $(this).val().length)
        })
    },

    onSubmitSuccess: function (model, response) {
        this.window.text(response.message)

        $('#email').val("")
        $('#letter-textarea').val("")
        $('h5').text('300 / 0')

        this.loader.removeClass('show')
        new AlertView().successWindowShow(this.window, 'Обращение отправлено')

        this.sendButtonDelay()
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message)
            new AlertView().errorWindowShow(this.window, error.responseJSON.message)
        this.loader.removeClass('show')

        this.sendButtonDelay()
    },

    sendButtonDelay: function() {
        let that = this
        setTimeout(function () {
            that.isFormSubmitting = false
            $('#send').prop('disabled', that.isFormSubmitting)
        }, 3000)
    }
})

let feedbackModel = new FeedbackModel()
new SendFeedback().setup({ model: feedbackModel })
