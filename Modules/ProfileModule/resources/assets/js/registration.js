import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {ProfileDomain as ProfileModel} from "./domains/profileDomain.js"

let EnterButtonView = Backbone.View.extend({
    el: '.button-enter',

    events: {
        'click': 'openPopup'
    },

    openPopup: function () {
        $('.popup-login').removeClass('disabled')
        $('.popup-content.auth').removeClass('disabled')
        $('html').addClass('hide-scroll')
        $('.container').addClass('mrg-container')
    }
})

let LoginPopupLinksView = Backbone.View.extend({
    el: '.popup',

    events: {
        'click a': 'handleTabClick',
        'mousedown': 'handleMouseDown'
    },

    handleTabClick: function (event) {
        event.preventDefault()
        const clickedClass = $(event.currentTarget).attr('class')

        let neededClass = {
            'register-link': this.showRegisterTab,
            'login-link':    this.showLoginTab,
            'restore-link':  this.showRestoreTab,
        }

        neededClass[clickedClass].call(this)
    },

    showRegisterTab: function () {
        $('.popup-content.auth, .popup-content.restore').addClass('disabled')
        $('.popup-content.register').removeClass('disabled')
    },

    showLoginTab: function () {
        $('.popup-content.register, .popup-content.restore').addClass('disabled')
        $('.popup-content.auth').removeClass('disabled')
    },

    showRestoreTab: function () {
        $('.popup-content.register, .popup-content.auth').addClass('disabled')
        $('.popup-content.restore').removeClass('disabled')
    },

    handleMouseDown: function (event) {
        let popup = $('.popup-content')
        if (!popup.is(event.target) && popup.has(event.target).length === 0) {
            $('html').removeClass('hide-scroll')
            $('.popup, .popup-content.auth, .popup-content.register, .popup-content.restore').addClass('disabled')
        }
    }
})

let RegistrationQuery = Backbone.View.extend({
    el: '#registration-form',

    events: {
        'submit': 'submitForm'
    },

    setup: function (options) {
        this.model  = options.model
        this.loader = $('.popup-login .popup-content.register .loader')
        this.submitButton = this.$('[type="submit"]')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    submitForm: function (event) {
        event.preventDefault()

        if (this.submitButton.prop('disabled')) {
            return
        }

        this.submitButton.prop('disabled', true)
        this.loader.addClass('show')

        this.model.set('action', 'create')
        this.model.set('name', $('#registration-name').val())
        this.model.set('email', $('#registration-email').val())
        this.model.set('password', $('#registration-password').val())
        this.model.set('remember', $('#registration-remember').is(":checked"))
        this.model.set('get_letter_release', $('#mailing').is(":checked"))
        this.model.set('timezone', Intl.DateTimeFormat().resolvedOptions().timeZone)

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.reload) {
            new AlertView().successWindowShow($('.popup-content.register h3'), 'Вход выполнен успешно')
            location.reload()
        }
        this.loader.removeClass('show')
        this.submitButton.prop('disabled', false)
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message)
            new AlertView().errorWindowShow($('.popup-content.register h3'), error.responseJSON.message)
        this.loader.removeClass('show')
        this.submitButton.prop('disabled', false)
    }
})

let AuthQuery = Backbone.View.extend({
    el: '#login-form',

    events: {
        'submit': 'submitForm'
    },

    setup: function (options) {
        this.model  = options.model
        this.loader = $('.popup-login .popup-content.auth .loader')
        this.submitButton = this.$('[type="submit"]')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    submitForm: function (event) {
        event.preventDefault()

        if (this.submitButton.prop('disabled')) {
            return
        }

        this.submitButton.prop('disabled', true)
        this.loader.addClass('show')

        this.model.set('action', 'login')
        this.model.set('email', $('#login-email').val())
        this.model.set('password', $('#login-password').val())
        this.model.set('remember', $('#login-remember').is(":checked"))
        this.model.set('timezone', Intl.DateTimeFormat().resolvedOptions().timeZone)

        this.model.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.reload) {
            new AlertView().successWindowShow($('.popup-content.auth h3'), 'Вход выполнен успешно')
            location.reload()
        }
        this.loader.removeClass('show')
        this.submitButton.prop('disabled', false)
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message)
            new AlertView().errorWindowShow($('.popup-content.auth h3'), error.responseJSON.message)
        this.loader.removeClass('show')
        this.submitButton.prop('disabled', false)
    }
})

let RestoreQuery = Backbone.View.extend({
    el: '#restore-form',

    events: {
        'submit': 'submitForm'
    },

    setup: function (options) {
        this.model  = options.model
        this.loader = $('.popup-login .popup-content.restore .loader')
        this.window = $('.popup-content.restore h3')
        this.submitButton = this.$('[type="submit"]')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    submitForm: function (event) {
        event.preventDefault()

        if (this.submitButton.prop('disabled')) {
            return
        }

        this.submitButton.prop('disabled', true)
        this.loader.addClass('show')

        this.model.set('action', 'restore')
        this.model.set('name', $('#restore-name').val())
        this.model.set('email', $('#restore-email').val())

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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

new EnterButtonView()
new LoginPopupLinksView()

let profileModel = new ProfileModel()
new RegistrationQuery().setup({model: profileModel})
new AuthQuery().setup({model: profileModel})
new RestoreQuery().setup({model: profileModel})
