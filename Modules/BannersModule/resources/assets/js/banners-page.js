import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {BannersDomain as BannersModel} from "./domains/bannersDomain.js"

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

let AddBanner = Backbone.View.extend({
    setup: function(options) {
        this.model    = options.model
        this.template = $('#template-banner-big-menu').html()
        this.textareaCounter = 1

        $('#add-banner').on('click', this.setTemplate.bind(this))
        $(document).on("change", "#bannerInput", this.setBannerImage.bind(this))
    },

    setTemplate: function() {
        let newTemplate = this.template.replace('id="new-0"', 'id="new-' + this.textareaCounter + '"')
        newTemplate = newTemplate.replace('id="edit-spoiler-0"', 'id="edit-spoiler-' + this.textareaCounter + '"')

        let blockId = Math.random().toString(36).substring(2,9)
        newTemplate = newTemplate.replaceAll('data-id="0"', 'data-id="' + blockId + '"')

        $('#big-banners-menu').append(newTemplate)
        this.textareaCounter++
    },

    setBannerImage: function (event) {
        event.stopPropagation()

        let fileInput = $(event.currentTarget)[0]
        let file      = fileInput.files[0]
        let reader    = new FileReader()

        let bannerId  = $(event.currentTarget).data('id')
        let bannerNew = this.model.get('bannerNewAdd') || {}
        let avatar    = $(".grid-block #avatar")

        let that = this
        reader.onload = function (event) {
            let template, type

            if (file.type.startsWith('video')) {
                template = '<div class="banner-newly-added" ' +
                    'data-id="' + bannerId + '">' +
                    '<video controls style="width: 100% height: 100% border: 2px dashed var(--pink) border-radius: 10px">' +
                    '<source src="' + event.target.result + '" type="' + file.type + '">' +
                    'Your browser does not support the video tag.' +
                    '</video>' +
                    '</div>'
                type = 'video'
            } else {
                template = '<div class="banner-newly-added" style="border: 2px dashed var(--pink) border-radius: 10px" ' +
                    'data-id="' + bannerId + '">' +
                    '<a href="' + event.target.result + '">' +
                    '<img src="' + event.target.result + '" alt="' + file.name +'">' +
                    '</a>' +
                    '</div>'
                type = 'image'
            }

            bannerNew[bannerId] = {
                result: event.target.result,
                type: type
            }

            avatar.attr("src", event.target.result)
            that.model.set('bannerNewAdd', bannerNew)

            let element = $(".banner-container[data-id='" + bannerId + "'] .banner-preview")
            if (!element.length)
                element = $(".banner-container[data-banner-id='" + bannerId + "'] .banner-preview")

            element.attr("style", "border: none !important")
            element.html(template)
        }

        reader.readAsDataURL(file)
    }
})

let RemoveBanner = Backbone.View.extend({
    setup: function(options) {
        this.model  = options.model
        this.isDeleteSubmitting = false
        this.loader = $('#main-loader')

        $(document).on('click', '.remove-banner', this.removeSoftlyBanner.bind(this))
        $(document).on('click', '.remove-forced-banner', this.removeForcedBanner.bind(this))
    },

    removeSoftlyBanner: function(event) {
        event.preventDefault()

        if (this.isDeleteSubmitting)
            return
        this.isDeleteSubmitting = true
        this.loader.addClass('show')

        let bannerContainer = $(event.currentTarget).closest('.banner-container')
        let bannerId = bannerContainer.find('#bannerInput').data('id')

        this.model.set('id', bannerId)

        if (bannerContainer.attr('id') !== undefined) {
            let bannerNew = this.model.get('bannerNewAdd')

            if (bannerNew[bannerId] != null || bannerNew[bannerId] === 'undefined') {
                delete bannerNew[bannerId]
                this.model.set('bannerNewAdd', bannerNew)
            }

            bannerContainer.remove()
            this.isDeleteSubmitting = false
        } else {
            this.model.set('action', 'removeSoftly')
            this.model.destroy({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (model, response) => {
                    if (response.success) {
                        this.loader.removeClass('show')
                        location.reload()
                    }

                    this.isDeleteSubmitting = false
                },
                error: (model, error) => {
                    if (error?.responseJSON?.message)
                        new AlertView().errorWindowShow($('.error_banner'), error.responseJSON.message)
                    this.loader.removeClass('show')
                    this.isDeleteSubmitting = false
                }
            })
        }
    },

    removeForcedBanner: function(event) {
        event.stopPropagation()

        if (this.isDeleteSubmitting)
            return
        this.isDeleteSubmitting = true
        this.loader.addClass('show')

        let bannerContainer = $(event.currentTarget).closest('.banner-container')
        let bannerUrl = bannerContainer.find('a').attr('href')
        let bannerId  = bannerContainer.find('#bannerInput').data('id')

        this.model.set({
            action: 'removeForced',
            id:     bannerId,
            url:    bannerUrl
        })

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    this.loader.removeClass('show')
                    location.reload()
                }

                this.isDeleteSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_banner'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isDeleteSubmitting = false
            }
        })
    }
})

let SaveBanners = Backbone.View.extend({
    setup: function(options) {
        this.model  = options.model
        this.isFormSubmitting = false
        this.loader = $('#main-loader')

        $('#banners-save').on('click', this.saveBanners.bind(this))
    },

    saveBanners: function(event) {
        event.stopPropagation()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.set('action', 'save')
        this.model.set('typeBanner', $(event.currentTarget).data('type-banner'))
        this.model.set('allBannerAdditional', this.getAllBannersAdditional())

        this.model.save(null, {
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
                    new AlertView().errorWindowShow($('.error_banner'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    },

    getAllBannersAdditional: function() {
        let allBannerAdditional = this.model.get('allBannerAdditional') || {}
        $('.banner-container.old').each(function() {
            let bannerId       = $(this).data('banner-id')
            let bannerPosition = $(this).find('.banner-position-input').val()
            let bannerHref     = $(this).find('.banner-href-input').val()
            let bannerName     = $(this).find('.banner-name-input').val()

            allBannerAdditional[bannerId] = {
                name:     bannerName,
                position: bannerPosition,
                href:     bannerHref
            }
        })

        return allBannerAdditional
    }
})

let ActivateBanners = Backbone.View.extend({
    setup: function(options) {
        this.model  = options.model
        this.isActivateSubmitting = false
        this.loader = $('#main-loader')

        $(document).on('click', '.banner-container .active-banner', this.toggleBanner.bind(this))
    },

    toggleBanner: function(event) {
        event.stopPropagation()

        if (this.isActivateSubmitting)
            return
        this.isActivateSubmitting = true
        this.loader.addClass('show')

        this.model.set('action', 'activate')
        this.model.set('id', $(event.currentTarget).closest(".banner-container").data('banner-id'))

        let parentBlock = $(event.currentTarget).closest('.banner-container')
            .find('.remove-forced-banner')

        this.model.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    $(event.currentTarget).remove()

                    if (response.active) {
                        let newActiveBanner = $('<i class="fas fa-check-circle fa-lg active-banner" ' +
                            'style="float: right" title="Активный"></i>')
                        parentBlock.after(newActiveBanner)
                    } else {
                        let newActiveBanner = $('<i class="fas fa-times-circle fa-lg active-banner" ' +
                            'style="float: right" title="Неактивный"></i>')
                        parentBlock.after(newActiveBanner)
                    }

                    this.loader.removeClass('show')
                }

                this.isActivateSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_banner'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isActivateSubmitting = false
            }
        })
    }
})


let bannersModel = new BannersModel()
new SpoilerHeadView().setup()

new AddBanner().setup({model: bannersModel})
new RemoveBanner().setup({model: bannersModel})
new SaveBanners().setup({model: bannersModel})
new ActivateBanners().setup({model: bannersModel})
