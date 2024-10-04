import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {ProfileDomain as ProfileModel} from "./domains/profileDomain.js"

let AvatarChangeView = Backbone.View.extend({
    el: '#fileInput',

    events: {
        'change': 'handleFileChange'
    },

    setup: function(options) {
        this.model = options.model
        $('#avatar-remove').on('click', this.handleFileRemove.bind(this))
    },

    handleFileChange: function() {
        let fileInput = this.el
        let file = fileInput.files[0]

        if (file instanceof Blob) {
            let reader = new FileReader()

            let that = this
            reader.onload = function (event) {
                $("#avatar").attr("src", event.target.result)
                $("#avatar-name").text(file.name)

                that.model.set('avatar_name', file.name)
                that.model.set('avatar', event.target.result)
            }

            reader.readAsDataURL(file)
        }
    },

    handleFileRemove: function (event) {
        event.preventDefault()
        $("#avatar").attr('src', '../../images/350.png')
        $("#avatar-name").text('Аватар не выбран')
        $("#file").val('')

        this.model.set('avatar_name', 'Аватар не выбран')
        this.model.set('avatar', '')
    }
})

let RoleChangeView = Backbone.View.extend({
    el: '.preview-detail-files',

    events: {
        'click': 'onClick'
    },

    setup: function(options) {
        this.model = options.model
    },

    onClick: function(e) {
        e.preventDefault()
        let $clickedElement = $(e.currentTarget)
        this.model.set('role', $clickedElement.data('role'))

        $('.preview-detail-files').each(function() {
            $(this).css("color", "black")
        })

        $clickedElement.css("color", "var(--pink)")
    }
})

let InputChangeView = Backbone.View.extend({
    el: '#profile-name, #status, #about, #cid, #email, #password, #mailing',

    events: {
        'change': 'handleInputChange'
    },

    setup: function(options) {
        this.model = options.model
        this.listenTo(this.model, 'change', this.render)
    },

    handleInputChange: function(event) {
        let $input = $(event.target)
        let attributeName = $input.attr('name')

        let attributeValue
        if ($input.attr('type') === 'checkbox') {
            attributeValue = $input.prop('checked')
        } else {
            attributeValue = $input.val()
        }

        this.model.set(attributeName, attributeValue)
    }
})

let ProfileUpdateQuery = Backbone.View.extend({
    el: '#profile-update',

    events: {
        'submit': 'submitForm'
    },

    setup: function (options) {
        this.model  = options.model
        this.loader = $('#main-loader')
        this.window = $('.error-profile')
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
        this.model.set('action', 'update')
        this.model.set('avatar_name', $('#avatar-name').text())

        this.model.save(null, {
            type: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.redirect_url) {
            new AlertView().successWindowShow(this.window, 'Профиль успешно сохранен')
            window.location.href = response.redirect_url
        }
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message)
            new AlertView().errorWindowShow(this.window, error.responseJSON.message)

        $('html, body').scrollTop(0)
        this.loader.removeClass('show')
        this.submitButton.prop('disabled', false)
    }
})

let profile = new ProfileModel()
profile.set('profileEncodeId', $('#profile-update').data('profile-id'))

new AvatarChangeView().setup({ model: profile })
new RoleChangeView().setup({ model: profile })
new InputChangeView().setup({ model: profile })
new ProfileUpdateQuery().setup({ model: profile })
