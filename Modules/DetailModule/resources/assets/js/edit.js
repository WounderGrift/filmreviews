import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {UnloaderView} from "../../../../../public/js/helpers/unloader.js"

import {DetailDomain as DetailModel} from "./domains/detailDomain.js"
import {PreviewDomain as PreviewModel} from "./domains/previewDomain.js"
import {ScreenshotDomain as ScreenshotModel} from "./domains/screenshotDomain.js"
import {FileDeleteDomain as FileDeleteModel} from "./domains/fileDeleteDomain.js"
import {FilmDeleteDomain as FilmDeleteModel} from "./domains/filmDeleteDomain.js"
import {DownloadDomain as DownloadModel} from "./domains/downloadDomain.js"

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

let filmNameAndOptions = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model
        this.initializefilmName()
        this.initializeCheckboxes()

        let view = this
        $(document).on('keyup', '#searchSeries', function () {
            let searchValue = $(this).val().toLowerCase();
            view.filterOptions(searchValue, $(this).closest('.series-dropdown').find('.options'));
        });
    },

    initializefilmName: function () {
        this.model.set('filmId', $('.blog .container').data('film-id'))
        this.model.set('filmName', $('#film-name').val().trim())

        $(document).on('input', '#film-name', () => {
            this.model.set('filmName', $('#film-name').val().trim())
        })
    },

    initializeCheckboxes: function () {
        const checkboxes = {
            isSponsor: '#is_sponsor',
            isSoft:    '#is_soft',
            isWeak:    '#is_weak',
            isWaiting: '#is_waiting'
        }

        let updatedCheckboxes = this.model.get('checkboxes') || {}
        for (let key in checkboxes) {
            updatedCheckboxes[key] = $(checkboxes[key]).is(":checked")
        }
        this.model.set('checkboxes', updatedCheckboxes)

        for (let key in checkboxes) {
            if (checkboxes.hasOwnProperty(key)) {
                $(document).on('change', checkboxes[key], (event) => {
                    updatedCheckboxes[key] = $(event.target).prop('checked')
                    this.model.set('checkboxes', updatedCheckboxes)
                })
            }
        }
    },

    filterOptions: function (searchValue, optionsContainer) {
        optionsContainer.find('.option').each(function () {
            const optionText = $(this).text().toLowerCase();
            if (optionText.indexOf(searchValue) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    }
})

let CtrlV = Backbone.View.extend({
    setup: function(options) {
        this.model = options.model
        this.dropArea = null

        $('#avatar').click((event) => {
            this.dropArea = $(event.currentTarget).data('target')
        })

        $('.poster-side #avatar').click((event) => {
            this.dropArea = $(event.currentTarget).data('target')
        })

        $('.gallery.exists').click((event) => {
            this.dropArea = $(event.currentTarget).data('target')
        })

        $(document).on('click', (event) => {
            let clickedElement = event.target
            if (!$(clickedElement).is('#avatar, .gallery.exists')) {
                this.dropArea = null
            }
        })

        $(document).on('paste', (event) => {
            let items = (event.originalEvent.clipboardData || event.clipboardData).items

            if (this.dropArea) {
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        let file = items[i].getAsFile()

                        if (this.dropArea === 'screenshots')
                            this.setScreenFromCtrlV(file)
                        else if (this.dropArea === 'detail')
                            this.setPreviewDetailFromCtrlV(file)
                        else if (this.dropArea === 'preview')
                            this.setPreviewGridFromCtrlV(file)
                        break
                    }
                }
            }
        })
    },

    setPreviewGridFromCtrlV: function(file) {
        let reader = new FileReader()
        let ava = $(".grid-block #avatar")

        reader.onload = (event) => {
            $('.grid-block #avatar-name').text(file.name)
            ava.attr("src", event.target.result)
            this.model.set('avatarGrid', event.target.result)
        }

        reader.readAsDataURL(file)
    },

    setPreviewDetailFromCtrlV: function(file) {
        let reader = new FileReader()
        let ava = $(".poster-side #avatar")

        reader.onload = (event) => {
            $('.poster-side #avatar-name').text(file.name)
            ava.attr("src", event.target.result)
            this.model.set('avatarPreview', event.target.result)
        }

        reader.readAsDataURL(file)
    },

    setScreenFromCtrlV: function(file) {
        let reader = new FileReader()
        let screenId = Math.random().toString(36).substring(2, 9)

        reader.onload = (event) => {
            let templateScreenshot = '<div class="photo-container newly-added" ' +
                'data-id="' + screenId + '">' +
                '<a href="' + event.target.result + '" data-fancybox="gallery" class="photo">' +
                '<img src="' + event.target.result + '" alt="{{ $film->name }}">' +
                '</a>' +
                '<div style="position: absolute; top: 0; right: 0;">' +
                '<i class="fas fa-times fa-lg remove remove-screen"></i>' +
                '</div>' +
                '</div>'

            let screenshotsNew = this.model.get('screenshotsNew')
            screenshotsNew[screenId] = event.target.result
            this.model.set('screenshotsNew', screenshotsNew)

            let newElement = $(templateScreenshot)
            let lastElement = $(".gallery.exists .custom-file-upload")
            newElement.insertBefore(lastElement)
        }

        reader.readAsDataURL(file)
    }
})

let PreviewGrid = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model
        this.gridPreviewInput = $(".grid-block #gridPreviewInput")
        this.gridPreviewInput.on("change", this.setAvatar.bind(this))
        $('.grid-block #avatar-remove').on('click', this.removeAvatar.bind(this))
    },

    setAvatar: function() {
        let fileInput = this.gridPreviewInput[0]
        let file      = fileInput.files[0]
        let reader    = new FileReader()
        let avatar    = $(".grid-block #avatar")

        let that = this
        reader.onload = function (event) {
            $('.grid-block #avatar-name').text(file.name)
            avatar.attr("src", event.target.result)
            that.model.set('avatarGrid', event.target.result)
        }

        reader.readAsDataURL(file)
    },

    removeAvatar: function(event) {
        event.preventDefault()
        $(".grid-block #avatar").attr('src', '/images/440.png')
        $(".grid-block #file").val("")
        $(".grid-block #avatar-name").text("Обложка не выбрана")
        this.model.set('avatarGrid', 'remove')
    }
})

let PreviewDetail = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model
        this.gridDetailInput = $(".summary-block #detailPreviewInput")
        this.gridDetailInput.on("change", this.setAvatar.bind(this))
        $('.summary-block #avatar-remove').on('click', this.removeAvatar.bind(this))
    },

    setAvatar: function() {
        let fileInput = this.gridDetailInput[0]
        let file      = fileInput.files[0]
        let reader    = new FileReader()
        let avatar    = $(".summary-block #avatar")

        let that = this
        reader.onload = function (event) {
            $('.summary-block #avatar-name').text(file.name)
            avatar.attr("src", event.target.result)
            that.model.set('avatarPreview', event.target.result)
            that.model.set('getAvatarPreviewFromScreen', false)
        }

        reader.readAsDataURL(file)
    },

    removeAvatar: function(event) {
        event.preventDefault()
        $(".summary-block #avatar").attr('src', '/images/730.png')
        $(".summary-block #file").val("")
        $(".summary-block #avatar-name").text("Обложка не выбрана")

        this.model.set('avatarPreview', 'remove')
        this.model.set('getAvatarPreviewFromScreen', false)
    }
})

let PreviewFilesChangeFromExisted = Backbone.View.extend({
    setup: function (options) {
        this.detailModel = options.model
        this.loader      = $('#main-loader')
        this.peviewModel = new PreviewModel({filmId: $('main .container').data('film-id')})

        this.isPreviewExistedSubmitting = false
        $('.preview-grid-files').on('click', (event) => {
            let clickedElement = $(event.currentTarget)
            let whatPreview    = 'grid'
            this.peviewModel.setPreviewData(clickedElement, whatPreview)
            this.previewFilesChangeFromExisted(whatPreview)
        })

        $('.preview-detail-files').on('click', (event) => {
            let clickedElement = $(event.currentTarget)
            let whatPreview    = 'detail'
            this.peviewModel.setPreviewData(clickedElement, whatPreview)
            this.previewFilesChangeFromExisted(whatPreview)
        })

        $('.preview-trailer-files').on('click', (event) => {
            let clickedElement = $(event.currentTarget)
            let whatPreview    = 'trailer'
            this.peviewModel.setPreviewData(clickedElement, whatPreview)
            this.previewFilesChangeFromExisted(whatPreview)
        })

        this.isPreviewExistedRemoveSubmitting = false
        $('.preview-grid-files .remove').on('click', (event) => {
            event.stopPropagation()
            let clickedElement = $(event.currentTarget)
            let whatPreview    = 'grid'
            this.peviewModel.setPreviewData(clickedElement, whatPreview)
            this.previewFilesRemoveFromExisted(whatPreview)
        })

        $('.preview-detail-files .remove').on('click', (event) => {
            event.stopPropagation()
            let clickedElement = $(event.currentTarget)
            let whatPreview    = 'detail'
            this.peviewModel.setPreviewData(clickedElement, whatPreview)
            this.previewFilesRemoveFromExisted(whatPreview)
        })

        $('.preview-trailer-files .remove').on('click', (event) => {
            event.stopPropagation()
            let clickedElement = $(event.currentTarget)
            let whatPreview    = 'trailer'
            this.peviewModel.setPreviewData(clickedElement, whatPreview)
            this.previewFilesRemoveFromExisted(whatPreview)
        })
    },

    previewFilesChangeFromExisted: function (whatPreview) {
        this.loader.addClass('show')

        if (this.isPreviewExistedSubmitting)
            return
        this.isPreviewExistedSubmitting = true

        this.peviewModel.set('action', 'setExisted')
        this.peviewModel.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    if (whatPreview === 'grid') {
                        $('.grid-box #avatar').attr('src', response.path)
                        this.detailModel.set('avatarGrid', response.path)
                    } else if (whatPreview === 'detail') {
                        $('.poster-box #avatar').attr('src', response.path)
                        this.detailModel.set('avatarPreview', response.path)
                    } else if (whatPreview === 'trailer') {
                        $('#videoContainer').attr('data-trailer', response.path)
                        $('#videoContainer img').attr('src', response.path)
                        $('#trailerPreviewEdit').val(response.path)
                        this.detailModel.set('previewTrailer', response.path)
                    }
                }

                this.loader.removeClass('show')
                this.isPreviewExistedSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)

                this.loader.removeClass('show')
                this.isPreviewExistedSubmitting = false
            }
        })
    },

    previewFilesRemoveFromExisted: function () {
        this.loader.addClass('show')

        if (this.isPreviewExistedRemoveSubmitting)
            return
        this.isPreviewExistedRemoveSubmitting = true

        this.peviewModel.set('action','removeExisted')
        this.peviewModel.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success)
                    this.peviewModel.get('that').closest('label').remove()
                this.loader.removeClass('show')
                this.isPreviewExistedRemoveSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isPreviewExistedRemoveSubmitting = false
            }
        })
    }
})

let SeriesDropdown = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model
        this.model.set('series', $("#searchSeries").val())

        const dropdownSeries = $(".series-dropdown")
        const selectedOptionsCategories = dropdownSeries.find(".selected-options")
        const optionsContainerCategories = dropdownSeries.find(".options")
        const optionsSeries = optionsContainerCategories.find(".option")

        selectedOptionsCategories.on('click', function () {
            optionsContainerCategories.toggle()
        })

        let that = this
        optionsSeries.each(function () {
            $(this).on('click', function () {
                optionsSeries.removeClass("selected")
                $(this).toggleClass("selected")
                that.updateSelectedOptionsSeries(selectedOptionsCategories, optionsContainerCategories)
            })
        })
    },

    updateSelectedOptionsSeries: function (selectedOptionsCategories, optionsContainerCategories) {
        let selectedItems = optionsContainerCategories.find(".selected").map(function () {
            return $(this).text()
        }).get()

        if (selectedItems.length === 0)
            selectedOptionsCategories.html('<span class="placeholder">' + +'</span>')
        else
            selectedOptionsCategories.val(selectedItems[0])
        this.model.set('series', selectedItems[0].trim())
    }
})

let CategoriesDropdown = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model
        this.model.set('categories', $('#categories-list').text().trim())

        const dropdownCategories = $(".category-dropdown")
        const selectedOptionsCategories  = dropdownCategories.find(".selected-options")
        const optionsContainerCategories = dropdownCategories.find(".options")
        const optionsCategories  = optionsContainerCategories.find(".option")

        selectedOptionsCategories.on('click', function () {
            optionsContainerCategories.toggle()
        })

        let that = this
        optionsCategories.each(function () {
            $(this).on('click', function () {
                $(this).toggleClass("selected")
                that.updateSelectedOptionsCategory(selectedOptionsCategories, optionsContainerCategories)
            })
        })
    },

    updateSelectedOptionsCategory: function (selectedOptionsCategories, optionsContainerCategories) {
        const selectedItems = optionsContainerCategories.find(".selected").map(function () {
            return $(this).text()
        }).get()

        let categories = null
        if (selectedItems.length === 0) {
            selectedOptionsCategories.html('<span class="placeholder">'
                + selectedOptionsCategories.data("default-value") + '</span>')
        } else {
            selectedOptionsCategories.html(selectedItems.join(", "))
            categories = selectedItems.join(", ").trim()
        }
        this.model.set('categories', categories)
    }
})

let SummaryView = Backbone.View.extend({
    setup: function (options) {
        this.model    = options.model
        this.template = '<li class="requirement-edit summary-fields">' +
            '<input id="summary-key" type="text" class="detail-summary-input summary-key" value="" placeholder="Ключ">' +
            '<input id="summary-val" type="text" class="detail-summary-input summary-val" value="" placeholder="Значение">' +
            '<i class="fas fa-times fa-lg remove summary"></i>' +
            '</li>'
        this.mediaEdit  = $("#media-edit")
        this.posterBox  = $(".poster-box.summary-block")
        this.posterSide = $('.poster-side')

        $('#add-summary').on('click', this.addSummary.bind(this))
        $(document).on("click", ".remove.summary", this.removeSummary.bind(this))
        $(window).on('resize', this.relocateSummary.bind(this))

        this.datepickerEvents()
        this.getSummaryObject()

        $(document).on('input', '.summary-fields input', () => this.getSummaryObject())

        this.model.set('dateRelease', $('#datepicker').val().trim())
        if (!this.model.get('dateRelease'))
            this.model.set('dateRelease', $('#datepicker_text').val().trim())
    },

    datepickerEvents: function () {
        let datepicker = $('#datepicker')
        let dr = new TheDatepicker.Datepicker(datepicker.get(0))
        dr.options.setInputFormat('d F Y')
        dr.render()

        datepicker.on('click', function () {
            dr.open()
        })

        let that = this
        dr.options.onSelect(function () {
            that.model.set('dateRelease', $('#datepicker').val().trim())
        })
    },

    addSummary: function (event) {
        event.preventDefault()
        $("#media-edit .requirement-list").append(this.template)
        this.getSummaryObject()
    },

    removeSummary: function (event) {
        event.preventDefault()
        $(event.currentTarget).closest(".requirement-edit").remove()
        this.getSummaryObject()
    },

    relocateSummary: function (event) {
        event.preventDefault()
        if ($(window).width() > 1000)
            this.posterSide.css('width', '60%')
        else {
            this.posterSide.css('width', '50%')

            if ($(window).width() > 768) {
                this.posterBox.append(this.mediaEdit)
            } else {
                this.posterSide.css('width', '60%')
                $(".col-12.order-2").append(this.mediaEdit)
            }
        }
    },

    getSummaryObject: function () {
        let summaryObject = {}
        let summary = $('#media-edit .summary-fields')

        summary.each(function () {
            let key = $(this).find('#summary-key').val().trim()
            if (key && !key.includes(':'))
                key = key + ':'

            let val = $(this).find('#summary-val').val().trim()

            if (key && val)
                summaryObject[key] = val
        })

        this.model.set('summaryObject', summaryObject)
    }
})

let RequireView = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model

        $('#add-requirements').on('click', this.addRequire.bind(this))
        $(document).on('click', '#remove-requirements', this.removeRequire.bind(this))

        this.getRequireObject()
        $(document).on('input', '.detail-summary-input', () => this.getRequireObject())
    },

    addRequire: function() {
        let templateRequireMin = '<li class="requirement-edit min-fields">' +
            '<input id="min-key" type="text" class="detail-summary-input" value="" placeholder="Ключ">' +
            '<input id="min-val" type="text" class="detail-summary-input" value="" placeholder="Значение">' +
            '</li>'

        let templateRequireMax = '<li class="requirement-edit max-fields">' +
            '<input id="max-key" type="text" class="detail-summary-input" value="" placeholder="Ключ">' +
            '<input id="max-val" type="text" class="detail-summary-input" value="" placeholder="Значение">' +
            '</li>'

        $(".requirements-container .min-requirements .requirement-list").append(templateRequireMin)
        $(".requirements-container .recommended-requirements .requirement-list").append(templateRequireMax)
        this.getRequireObject()
    },

    removeRequire: function() {
        $(".requirements-container .system-requirements .requirement-list .requirement-edit:last-child").remove()
        this.getRequireObject()
    },

    getRequireObject: function() {
        let minObject = {}
        let min = $('.requirements-container .min-fields')

        min.each(function() {
            let key = $(this).find('#min-key').val().trim()
            if (key && !key.includes(':'))
                key = key + ':'

            let val = $(this).find('#min-val').val().trim()

            if (key && val)
                minObject[key] = val
        })

        let maxObject = {}
        let max = $('.requirements-container .max-fields')

        max.each(function() {
            let key = $(this).find('#max-key').val().trim()
            if (key && !key.includes(':'))
                key = key + ':'

            let val = $(this).find('#max-val').val().trim()

            if (key && val)
                maxObject[key] = val
        })

        this.model.set('requireObject', {
            min: minObject,
            max: maxObject,
        })
    }
})

let DescriptionEditor = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model

        let that = this
        $('#edit-description').summernote({
            height: 400,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['view', ['codeview']]
            ],
            callbacks: {
                onInit: function() {
                    $(this).summernote('code', that.model.get('description'))
                },
                onChange: function(contents) {
                    $('.text-show').html(contents)
                    that.model.set('description', contents)
                }
            }
        })
    }
})

let PlayVideoView = Backbone.View.extend({
    el: '#playButton',

    events: {
        'click': 'play'
    },

    setup: function (options) {
        this.model = options.model
        $('#save-trailer').on('click', this.looking.bind(this))
    },

    play: function () {
        let trailerUrl = this.model.get('trailer')

        $("#videoContainer").html(`
            <div class="video-responsive">
                <iframe width="560" height="315" src="${trailerUrl}"
                frameborder="0" allowfullscreen></iframe>
            </div>
        `)
    },

    looking: function() {
        let newTrailer = $('#trailer_edit').val() + "?autoplay=1&mute=1&rel=0&showinfo=0&iv_load_policy=3"
        $('#videoContainer').attr('data-trailer', newTrailer)

        let previewTrailer = $('#trailerPreviewEdit').val()
        $('#videoContainer img').attr('src', previewTrailer)

        this.model.set('trailer', newTrailer)
        this.model.set('previewTrailer', previewTrailer)

        let iframe = $('.video-responsive iframe')
        iframe.attr('src', '')
        iframe.attr('src', newTrailer)
    }
})

let Screenshots = Backbone.View.extend({
    el: '#screenshotInput',

    events: {
        'change': 'changeInput'
    },

    setup: function (options) {
        this.model = options.model
        this.screenshotModel = options.screenshotModel
        this.isScreenChangeSubmitting = false

        this.loader = $('#main-loader')
        $('.gallery.exists').on('click', '.photo-container .remove-screen', (event) =>
            this.removeScreenLight(event, 'remove'))

        $('.gallery.removed').on('click', '.photo-container .remove-screen', (event) =>
            this.removeScreenLight(event, 'return'))

        this.checkRemovedScreen()

        let allGallery = $('.gallery.exists, .gallery.removed, .gallery.extra')
        allGallery.on('click', '.photo-container .remove-screen-force',
            this.removeScreenForced.bind(this))
        allGallery.on('click', '.photo-container .select-preview',
            this.selectPreviewDetailFromScreenshots.bind(this))
        allGallery.on('click', '.photo-container .select-preview-trailer',
            this.selectTrailerPreviewFromScreenshots.bind(this))
    },

    changeInput: function() {
        let fileInput = this.el
        let files = fileInput.files

        for (let i = 0; i < files.length; i++) {
            let file = files[i]
            let reader   = new FileReader()
            let screenId = Math.random().toString(36).substring(2, 9)

            let that = this
            reader.onload = (function (screenId) {
                return function (event) {
                    let templateScreenshot = '<div class="photo-container newly-added" ' +
                        'data-id="'  + screenId + '">' +
                        '<a href="'  + event.target.result + '" data-fancybox="gallery" class="photo">' +
                        '<img src="' + event.target.result + '" alt="{{ $film->name }}">' +
                        '</a>' +
                        '<div style="position: absolute; top: 0; right: 0;">' +
                        '<i class="fas fa-times fa-lg remove remove-screen"></i>' +
                        '</div>' +
                        '</div>'

                    let screenshotsNew = that.model.get('screenshotsNew')
                    screenshotsNew[screenId] = event.target.result
                    that.model.set('screenshotsNew', screenshotsNew)

                    let newElement  = $(templateScreenshot)
                    let lastElement = $(".gallery.exists .custom-file-upload")
                    newElement.insertBefore(lastElement)
                }
            })(screenId)

            reader.readAsDataURL(file)
        }
    },

    removeScreenForced: function(event) {
        event.preventDefault()

        if (this.isScreenChangeSubmitting)
            return
        this.isScreenChangeSubmitting = true
        this.loader.addClass('show')

        let photoContainer = $(event.target).closest(".photo-container")

        this.screenshotModel.set('action', 'removeForced')
        this.screenshotModel.set('id', photoContainer.data('id') ?? 0)
        this.screenshotModel.set('url', photoContainer.find('img').attr('src'))

        this.screenshotModel.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    photoContainer.remove()
                    this.checkRemovedScreen()
                }

                this.loader.removeClass('show')
                this.isScreenChangeSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isScreenChangeSubmitting = false
            }
        })
    },

    removeScreenLight: function(event, action) {
        event.preventDefault()
        if (this.isScreenChangeSubmitting)
            return
        this.isScreenChangeSubmitting = true
        this.loader.addClass('show')

        let photoContainer = $(event.target).closest(".photo-container")

        this.screenshotModel.set('action', 'removeSoftly')
        this.screenshotModel.set('id', photoContainer.data('id'))

        if (photoContainer.hasClass("newly-added")) {
            let screenshotsNew = this.model.get('screenshotsNew') || {}
            delete screenshotsNew[this.screenshotModel.get('id')]
            this.model.set('screenshotsNew', screenshotsNew)

            photoContainer.remove()
            this.isScreenChangeSubmitting = false
            this.loader.removeClass('show')
        } else {
            this.screenshotModel.destroy({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (model, response) => {
                    if (response.success) {
                        if (action === 'return') {
                            let existsGallery = $(".gallery.exists .custom-file-upload")
                            photoContainer.detach().insertBefore(existsGallery)
                        } else if (action === 'remove') {
                            let deletedGallery = $(".gallery.removed")
                            photoContainer.detach().appendTo(deletedGallery)
                        }

                        this.checkRemovedScreen()
                        this.loader.removeClass('show')
                    }

                    this.isScreenChangeSubmitting = false
                },
                error: (model, error) => {
                    if (error?.responseJSON?.message)
                        new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)
                    this.loader.removeClass('show')
                    this.isScreenChangeSubmitting = false
                }
            })
        }

        if (action === 'remove' && $(".gallery.removed .photo-container").length > 0)
            $('.substrate.empty-removed-gallery').remove()
    },

    checkRemovedScreen: function() {
        if ($(".gallery.removed .photo-container").length < 1) {
            if ($(".empty-removed-gallery").length < 1) {
                let templateEmptyRemovedScreen = '<div class="substrate empty-removed-gallery">\n' +
                    '<h3>Удаленных скриншотов нет</h3>\n' +
                    '</div>'

                $(".gallery.removed").append(templateEmptyRemovedScreen)
            }
        } else {
            $('.empty-removed-gallery').remove()
        }

        let extraGallery = $(".gallery.extra")
        if (extraGallery.find(".photo-container").length < 1) {
            extraGallery.remove()
            $(".extra-title").remove()
        }
    },

    selectPreviewDetailFromScreenshots: function(event) {
        event.preventDefault()
        let photoContainer = $(event.target).closest(".photo-container")
        let imgSrc = photoContainer.find('img').attr('src')

        let avatar = $(".summary-block #avatar")
        $('.summary-block #avatar-name').text(imgSrc)
        avatar.attr("src", imgSrc)

        this.model.set('avatarPreview', imgSrc)
        this.model.set('getAvatarPreviewFromScreen', true)
    },

    selectTrailerPreviewFromScreenshots: function(event) {
        event.preventDefault()
        let photoContainer = $(event.target).closest(".photo-container")
        let imgSrc = photoContainer.find('img').attr('src')

        $('#videoContainer img').attr('src', imgSrc)
        $('#trailerPreviewEdit').val(imgSrc)

        this.model.set('previewTrailer', imgSrc)
    }
})

let SetupRepackersDropdown = Backbone.View.extend({
    setup: function (options) {
        this.templateId = options.templateId

        this.dropdownRepacks = $(this.templateId + " .repacks-dropdown")
        this.selectedOptionsRepacks  = this.dropdownRepacks.find(".selected-options")
        this.optionsContainerRepacks = this.dropdownRepacks.find(".options")
        this.optionsRepacks  = this.optionsContainerRepacks.find(".option")

        this.selectedOptionsRepacks.on('click', () => {
            this.optionsContainerRepacks.toggle()
        })

        let view = this
        $(document).on('keyup', this.templateId + ' #searchRepacks', function () {
            let searchValue = $(this).val().toLowerCase();
            view.filterOptions(searchValue, $(this).closest('.repacks-dropdown'));
        });

        this.optionsRepacks.each(function () {
            $(this).on('click', function () {
                view.optionsRepacks.removeClass("selected")
                $(this).addClass("selected")
                view.updateSelectedOptionsRepacks()
            })
        })
    },

    filterOptions: function (searchValue) {
        this.optionsRepacks.each(function () {
            const optionText = $(this).text().toLowerCase();
            if (optionText.indexOf(searchValue) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    },

    updateSelectedOptionsRepacks: function () {
        const selectedItems = this.optionsContainerRepacks.find(".selected").map(function () {
            return $(this).text()
        }).get()

        if (selectedItems.length === 0)
            this.selectedOptionsRepacks.html('<span class="placeholder">' +  + '</span>')
        else
            this.selectedOptionsRepacks.val(selectedItems[0])
    }
})

let FileViewOld = Backbone.View.extend({
    el: '.download-container.old',

    setup: function (options) {
        this.model = options.model
        new SetupRepackersDropdown().setup({templateId: '#' + $(this.el).attr('id')})

        this.fileOldObject = {}

        let files = $('.download-container.old:visible')
        let view = this

        files.each(function () {
            view.getOldfileInfo($(this))
        })

        this.$el.on('click input', '.size, .version, .option, #sponsor, #fileInput, #searchRepacks',
            (event) => {
                let parentElement = $(event.currentTarget).closest('.download-container.old')
                this.getOldfileInfo(parentElement)
            })
    },

    startInitIOldfile: function () {
        this.$el.each(function (index, element) {
            this.initOldfile(element)
        }.bind(this))
    },

    initOldfile: function (element) {
        let view = this
        $(element).find('textarea').each(function () {
            let editorId = $(this).attr('id')

            $('#'+editorId).summernote({
                height: 400,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['view', ['codeview']]
                ],
                callbacks: {
                    onInit: function() {
                        let id = $(element).data('file-id')
                        $(this).summernote('code', view.model.get('filesOld')[id].additional_info)
                    },
                    onChange: function(contents, element) {
                        $(element).parent().parent().parent().find('.spoiler-description').html(contents)

                        let thisSpoiler = $(element).parent().parent().parent().parent()
                        let spoilerContent = thisSpoiler.find('.spoiler-content')
                        let maxHeight = thisSpoiler.hasClass('open')
                            ? spoilerContent.prop('scrollHeight') + 'px' : '0'
                        spoilerContent.css('max-height', maxHeight)

                        view.getOldfileInfo($('#'+editorId).parent().parent().parent())
                    }
                }
            })
        })
    },

    getOldfileInfo: function (object) {
        object = $(object)

        let blockId = object.data('file-id')
        this.fileOldObject[blockId] = {
            repacker: object.find("#searchRepacks").val(),
            size: object.find('.size').val().trim(),
            version: object.find('.version').val().trim(),
            additional_info: object.find('.spoiler-description').html()
        }

        this.model.set('filesOld', this.fileOldObject)
    }
})

let AddNewfile = Backbone.View.extend({
    el: '#add-file',

    setup: function (options) {
        this.textareaCounter = 1
        this.model = options.model
        this.oldTemplate = $('#template-download').html()

        $('#add-file').on('click', () => this.addfile())
    },

    addfile: function(modelfileId = null) {
        let newTemplate = this.oldTemplate.replace('id="new-0"', 'id="new-' + this.textareaCounter + '"')

        newTemplate = newTemplate.replace('id="edit-spoiler-0"',
            'id="edit-spoiler-' + this.textareaCounter + '"')
        let blockId = modelfileId ?? Math.random().toString(36).substring(2, 9)
        newTemplate = newTemplate.replaceAll('data-id="0"', 'data-id="' + blockId + '"')

        $('#files').append(newTemplate)

        let templateId = $(`[data-id="${blockId}"]`).attr('id')
        new SetupRepackersDropdown().setup({templateId: '#'+templateId})
        this.initNewfile(blockId)

        $(document).on('click input', '.size, .version, .option, #sponsor, #fileInput, #searchRepacks',
            (event) => this.saveModelNewfile(event, blockId))
    },

    initNewfile: function (blockId) {
        let templateId = '#new-' + this.textareaCounter

        let textarea = $(templateId + ' textarea')
        let editorId = $(textarea).attr('id')
        let view = this

        $('#'+editorId).summernote({
            height: 400,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['view', ['codeview']]
            ],
            callbacks: {
                onInit: function() {
                    view.saveModelNewfile(null, blockId)

                    $(this).summernote('code', view.model.get('filesNew')[blockId].additional_info)
                    $(templateId).find('.spoiler-description').html(view.model.get('filesNew')[blockId].additional_info)
                },
                onChange: function(contents) {
                    $(templateId).find('.spoiler-description').html(contents)

                    let thisSpoiler = $(templateId).find('.spoiler')
                    let spoilerContent = thisSpoiler.find('.spoiler-content')
                    let maxHeight = thisSpoiler.hasClass('open')
                        ? spoilerContent.prop('scrollHeight') + 'px' : '0'
                    spoilerContent.css('max-height', maxHeight)

                    view.saveModelNewfile(null, blockId)
                }
            }
        })

        this.textareaCounter++
    },

    saveModelNewfile: function (event = null, blockId) {
        if (event)
            event.stopPropagation()

        let editorObject = blockId
            ? $('[data-id="' + blockId + '"]').find('[id^="edit-spoiler-"]')
            : 'edit-spoiler-' + (this.textareaCounter - 1)

        let additionalInfo = editorObject.summernote('code')
        let filesNew    = this.model.get('filesNew') || {}

        if (event && event.target.files && event.target.files.length > 0 && event.target.id === 'fileInput') {
            let formData = new FormData()
            for (let i = 0; i < event.target.files.length; i++) {
                formData.append('file[' + blockId + '][]', event.target.files[i]);
            }

            filesNew[blockId]['files'] = formData
        }

        const isString = (value) => typeof value === 'string'

        filesNew[blockId] = {
            ...filesNew[blockId],
            repacker: (event && $(event.target).hasClass('option')
                ? $(event.target).text().trim() : filesNew[blockId]?.repacker) || null,
            size: (event && $(event.target).hasClass('size')
                ? $(event.target).val().trim() : filesNew[blockId]?.size) || '0.0 ГБ',
            version: (event && $(event.target).hasClass('version')
                ? $(event.target).val().trim() : filesNew[blockId]?.version) || 'v0.0',
            sponsor_url: (event && $(event.target).attr('id') === "sponsor"
                ? $(event.target).val().trim() : filesNew[blockId]?.sponsor_url) || false,
            additional_info: isString(additionalInfo)
                ? (additionalInfo.trim() !== "" && additionalInfo.trim() !== '<p><br></p>')
                    ? additionalInfo
                    : filesNew[blockId]?.additional_info || ""
                : filesNew[blockId]?.additional_info || ""
        }

        this.model.set('filesNew', filesNew)
    }
})

let Removefile = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model
        this.fileDeleteModel = options.fileDeleteModel
        this.isDeleteSubmitting = false

        this.loader = $('#main-loader')

        $(document).on('click', '.remove-file', this.removefileLight.bind(this))
        $(document).on('click', '.remove-force-file', this.removefileForced.bind(this))
    },

    removefileLight: function(event) {
        event.stopPropagation()

        if (this.isDeleteSubmitting)
            return
        this.isDeleteSubmitting = true
        this.loader.addClass('show')

        let downloadContainer = $(event.target).closest('.download-container')

        if (downloadContainer.attr('id').indexOf('new') !== -1) {
            let fileId = downloadContainer.data('id')
            let filesNew = this.model.get('filesNew')

            if (filesNew[fileId] != null || filesNew[fileId] === 'undefined') {
                delete filesNew[fileId]
                this.model.set('filesNew', filesNew)
            }

            downloadContainer.remove()
            this.loader.removeClass('show')
            this.isDeleteSubmitting = false
        } else {
            this.fileDeleteModel.set('action', 'removeSoftly')
            this.fileDeleteModel.set('id', downloadContainer.data('file-id'))

            this.fileDeleteModel.destroy({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (model, response) => {
                    if (response.success) {
                        let removedSign = downloadContainer.find('h2.removed-sign')[0]

                        if (removedSign) {
                            removedSign.remove()
                        } else {
                            downloadContainer.find('.spoiler').prepend(
                                '<h2 class="removed-sign" style="color: black">УДАЛЕНО</h2>'
                            )
                        }
                    }

                    this.loader.removeClass('show')
                    this.isDeleteSubmitting = false
                },
                error: (model, error) => {
                    if (error?.responseJSON?.message)
                        new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)

                    this.loader.removeClass('show')
                    this.isDeleteSubmitting = false
                }
            })
        }
    },

    removefileForced: function(event) {
        event.stopPropagation()

        if (this.isDeleteSubmitting)
            return
        this.isDeleteSubmitting = true
        this.loader.addClass('show')

        let downloadContainer = $(event.target).closest('.download-container')
        this.fileDeleteModel.set('action', 'removeForced')
        this.fileDeleteModel.set('fileUrl', downloadContainer.find('a').attr('href'))
        this.fileDeleteModel.set('id', downloadContainer.data('file-id') ?? 0)

        if (this.model.get('filesOld')[downloadContainer.data('file-id')] != null) {
            let filesOld = this.model.get('filesOld')
            delete filesOld[downloadContainer.data('file-id')]
            this.model.set('filesOld', filesOld)
        }

        this.fileDeleteModel.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success)
                    downloadContainer.remove()
                this.loader.removeClass('show')
                this.isDeleteSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isDeleteSubmitting = false
            }
        })
    }
})

let SaveAndLoadModel = Backbone.View.extend({
    setup: function (options) {
        this.model   = options.model
        this.fileViewOld = options.fileViewOld
        this.notSave = false
        this.loader  = $('#main-loader')

        let storedData = localStorage.getItem(this.model.get('filmId'))
        if (storedData) {
            let data = JSON.parse(storedData)
            this.model.set(data)
            this.loadModel()
        } else {
            $('#loading-model').remove()
            this.fileViewOld.startInitIOldfile()
        }

        if (typeof window.obUnloader != 'object') {
            window.obUnloader = new UnloaderView({el: $(document)})
        }

        $(window).on('beforeunload', this.saveModel.bind(this))
        $(document).on('click', '#clear-model', this.clearStorage.bind(this))

        $(window).on('keydown', (e) => {
            if (e.ctrlKey && e.keyCode === 82) {
                e.preventDefault()
                this.clearStorage()
            }
        })
    },

    loadModel: function () {
        this.loader.addClass('show')

        $('#film-name').val(this.model.get('filmName'))

        let checkboxes = this.model.get('checkboxes')
        $('#is_sponsor').prop('checked', checkboxes.isSponsor)
        $('#is_soft').prop('checked', checkboxes.isSoft)
        $('#is_weak').prop('checked', checkboxes.isWeak)
        $('#is_waiting').prop('checked', checkboxes.isWaiting)

        $('#datepicker').val(this.model.get('dateRelease'))
        $('#datepicker_text').val(this.model.get('dateRelease'))
        $('#categories-list span').text(this.model.get('categories'))
        this.selectCategories()

        $('#searchSeries').val(this.model.get('series'))

        let gridBox = $('.grid-box #avatar')
        if (this.model.get('avatarGrid') !== "" && this.model.get('avatarGrid') !== "remove")
            gridBox.attr('src', this.model.get('avatarGrid'))
        else if (this.model.get('avatarGrid') === "remove")
            gridBox.attr('src', '/images/440.png')

        let posterBox = $('.poster-box #avatar')
        if (this.model.get('avatarPreview') !== "" && this.model.get('avatarPreview') !== "remove")
            posterBox.attr('src', this.model.get('avatarPreview'))
        else if (this.model.get('avatarPreview') === "remove")
            posterBox.attr('src', '/images/730.png')

        let videoContainer = $('#videoContainer')
        videoContainer.attr('data-trailer', videoContainer.attr('data-trailer'))
        let videoImg = $('#videoContainer img')

        if (this.model.get('previewTrailer') !== "")
            videoImg.attr('src', this.model.get('previewTrailer'))
        let trailerPreview = $('#trailerPreviewEdit')
        trailerPreview.val(videoImg.attr('src'))

        this.getSummary()
        $('.text-show').html(this.model.get('description'))

        this.getRequire()
        this.getScreenshots()

        this.getfile('filesOld', '#old-')
        this.getfile('filesNew', '#new-')
        this.loader.removeClass('show')
    },

    getfile: function(type, id) {
        for (let key in this.model.get(type)) {
            if (this.model.get(type).hasOwnProperty(key)) {
                let object = null
                if (type === 'filesNew') {
                    addNewfile.addfile(key)
                    object = $(id + (addNewfile.textareaCounter - 1))
                } else {
                    object = $(id + key)
                }

                object.find('.size').val(this.model.get(type)[key].size)
                object.find('.version').val(this.model.get(type)[key].version)
                if (object.find('#sponsor'))
                    object.find('#sponsor').val(this.model.get(type)[key].sponsor_url)

                let additionalInfo = this.model.get(type)[key].additional_info
                let decodedHtml    = $('<div/>').html(additionalInfo).text()
                object.find('.spoiler-description').html(decodedHtml)

                const dropdownRepacks = object.find(".repacks-dropdown")
                const selectedOptionsRepacks  = dropdownRepacks.find(".selected-options")
                const optionsContainerRepacks = dropdownRepacks.find(".options")
                const optionsRepacks  = optionsContainerRepacks.find(".option")

                let repackerArray = this.model.get(type)[key]?.repacker || []
                optionsRepacks.each(function () {
                    let repackText = $(this).text()
                    if (repackerArray.includes(repackText)) {
                        $(this).addClass('selected')
                        selectedOptionsRepacks.val(repackText)
                    } else {
                        $(this).removeClass('selected')
                    }
                })
            }
        }

        if (type === 'filesOld') {
            this.fileViewOld.startInitIOldfile()
        }
    },

    getScreenshots: function() {
        let screenshotsNew = {}
        for (let key in this.model.get('screenshotsNew')) {
            if (this.model.get('screenshotsNew').hasOwnProperty(key)) {
                let screen = this.model.get('screenshotsNew')[key]
                let templateScreenshot = '<div class="photo-container newly-added" ' +
                    'data-id="' + key + '">' +
                    '<a href="' + screen + '" data-fancybox="gallery" class="photo">' +
                    '<img src="'+ screen + '" alt="{{ $film->name }}">' +
                    '</a>' +
                    '<div style="position: absolute; top: 0; right: 0;">' +
                    '<i class="fas fa-times fa-lg remove remove-screen"></i>' +
                    '</div>' +
                    '</div>'

                screenshotsNew[key] = screen

                let newElement  = $(templateScreenshot)
                let lastElement = $(".gallery.exists .custom-file-upload")
                newElement.insertBefore(lastElement)
            }
        }

        this.model.set('screenshotsNew', screenshotsNew)
    },

    selectCategories: function () {
        const dropdownCategories = $(".category-dropdown")
        const optionsContainerCategories = dropdownCategories.find(".options")
        const optionsCategories  = optionsContainerCategories.find(".option")

        let categories = this.model.get('categories') || []
        optionsCategories.each(function () {
            if (categories.includes($(this).text())) {
                $(this).addClass('selected')
            } else {
                $(this).removeClass('selected')
            }
        })
    },

    getSummary: function () {
        $('#summary').empty()

        let that = this
        $.each(that.model.get('summaryObject'), function (title, value) {
            let createFiled = true
            $('.summary-fields').each(function () {
                let key = $(this).find('.summary-key').val().trim()
                if (key === title) {
                    $(this).find('.summary-val').val(value)
                    createFiled = false
                }
            })

            if (!createFiled)
                return

            let templateSummary = '<li class="requirement-edit summary-fields">' +
                '<input id="summary-key" type="text" class="detail-summary-input summary-key" value="'
                + title + '" placeholder="Ключ">' +
                '<input id="summary-val" type="text" class="detail-summary-input summary-val" value="'
                + value + '" placeholder="Значение">' +
                '<i class="fas fa-times fa-lg remove summary"></i>' +
                '</li>'

            $("#media-edit .requirement-list").append(templateSummary)
        })
    },

    getRequire: function() {
        $('.recommended-requirements .requirement-list').empty()
        $('.min-requirements .requirement-list').empty()

        if (this.model.get('requireObject')) {
            $.each(this.model.get('requireObject'), function (title1, value1) {
                $.each(value1, function (title2, value2) {
                    if (title1 === 'min') {
                        let templateRequireMin = '<li class="requirement-edit min-fields">' +
                            '<input id="min-key" type="text" class="detail-summary-input" value="' + title2 + '">' +
                            '<input id="min-val" type="text" class="detail-summary-input" value="' + value2 + '">' +
                            '</li>'

                        $(".requirements-container .min-requirements .requirement-list").append(templateRequireMin)
                    } else if (title1 === 'max') {
                        let templateRequireMax = '<li class="requirement-edit max-fields">' +
                            '<input id="max-key" type="text" class="detail-summary-input" value="' + title2 + '">' +
                            '<input id="max-val" type="text" class="detail-summary-input" value="' + value2 + '">' +
                            '</li>'

                        $(".requirements-container .recommended-requirements .requirement-list")
                            .append(templateRequireMax)
                    }
                })
            })
        }
    },

    saveModel: function () {
        if (this.notSave)
            return
        localStorage.setItem(this.model.get('filmId'), JSON.stringify(this.model))
    },

    clearStorage: function () {
        this.notSave = true

        window.obUnloader.resetUnload()
        localStorage.removeItem(this.model.get('filmId'))
        location.reload()
    }
})

let Releasefilm = Backbone.View.extend({
    el: '#release-detail',

    events: {
        'click': 'release'
    },

    setup: function (options) {
        this.model  = options.model
        this.isFormSubmitting = false
        this.loader = $('#main-loader')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    release: function () {
        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.set('release', true)
        this.model.set('previewTrailer', $('#trailerPreviewEdit').val().trim())
        this.model.set('trailer', $('#trailer_edit').val().trim())
        this.model.set('action', 'release')

        const formData    = new FormData()
        const filesNew = this.model.get('filesNew') || {};
        for (const blockId in filesNew) {
            if (filesNew.hasOwnProperty(blockId)) {
                const fileData = filesNew[blockId];

                formData.append(`filesNew[${blockId}][repacker]`, fileData.repacker);
                formData.append(`filesNew[${blockId}][size]`, fileData.size);
                formData.append(`filesNew[${blockId}][version]`, fileData.version);
                formData.append(`filesNew[${blockId}][sponsor_url]`, fileData.sponsor_url);
                formData.append(`filesNew[${blockId}][additional_info]`, fileData.additional_info);

                if (fileData.files) {
                    for (const [key, value] of fileData.files.entries()) {
                        formData.append(`filesNew[${blockId}][files][]`, value);
                    }
                }
            }
        }

        formData.append(`detail`, JSON.stringify(this.model.toJSON()))

        this.model.save(null, {
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.redirect_url) {
            if (window.obUnloader instanceof UnloaderView)
                window.obUnloader.resetUnload()

            $(window).off('beforeunload')
            localStorage.removeItem(this.model.get('filmId'))
            window.location.href = response.redirect_url
        }

        this.loader.removeClass('show')
        this.isFormSubmitting = false
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message)
            new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)
        this.loader.removeClass('show')
        this.isFormSubmitting = false
    }
})

let Removefilm = Backbone.View.extend({
    el: '#delete-film',

    events: {
        'click': 'removefilm'
    },

    setup: function (options) {
        this.model  = options.model
        this.model.set('id', $(this.el).data('film-id'))
        this.loader = $('#main-loader')

        this.isFormSubmitting = false
    },

    removefilm: function (event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.redirect_url) {
                    if (window.obUnloader instanceof UnloaderView)
                        window.obUnloader.resetUnload()
                    window.location.href = response.redirect_url
                }
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_save_detail'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    }
})

let DownloadActionView = Backbone.View.extend({
    el: '.download',

    events: {
        'click': 'download'
    },

    setup: function() {
        this.downloadQueue = new DownloadCollection()
        this.debounceDownloadTimeout = null
    },

    download: function(event) {
        let download = $(event.currentTarget)
        let count    = download.closest('.download-container').find('.download-count span')
        let errorWindows = download.closest('.download-container').find('.error_download')
        let file_id   = download.data('code')

        let existingItem = this.downloadQueue.findWhere({
            file_id: file_id
        })

        if (existingItem) {
            return false
        } else {
            this.downloadQueue.add({
                file_id: file_id,
                count: count,
                errorWindows: errorWindows
            })
        }

        clearTimeout(this.debounceDownloadTimeout)
        this.debounceDownloadTimeout = setTimeout(() => {
            this.processDownloadQueue()
        }, 300)
    },

    processDownloadQueue: async function() {
        for (let item of this.downloadQueue.models) {
            await this.processItem(item)
            await this.delay(300)
        }
    },

    processItem: function(item) {
        return new Promise((resolve, reject) => {
            item.save(null, {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {
                    let currentValue = parseInt(item.get('count').text())

                    if (!isNaN(currentValue)) {
                        currentValue++
                        item.get('count').text(currentValue)
                    }

                    if (response && response.get('file_url') && response.get('file_name')) {
                        let tempLink    = document.createElement('a')
                        tempLink.href   = response.get('file_url')
                        tempLink.target = '_blank'
                        tempLink.download = response.get('file_name')
                        document.body.appendChild(tempLink)
                        tempLink.click()
                        document.body.removeChild(tempLink)
                    } else {
                        new AlertView().errorWindowShow(item.get('errorWindows'), 'Не удалось скачать файл')
                    }

                    this.downloadQueue.remove(item)
                    resolve(true)
                },
                error: (model, error) => {
                    if (error?.responseJSON?.message)
                        new AlertView().errorWindowShow(item.get('errorWindows'), error.responseJSON.message)

                    this.downloadQueue.remove(item)
                    reject(error.responseJSON.message)
                }
            })
        })
    },

    delay: function(ms) {
        return new Promise(resolve => setTimeout(resolve, ms))
    }
})

let detailModel = new DetailModel()
new SpoilerHeadView().setup()
new CtrlV().setup({model: detailModel})

new filmNameAndOptions().setup({model: detailModel})
new PreviewGrid().setup({model: detailModel})
new PreviewDetail().setup({model: detailModel})

new PreviewFilesChangeFromExisted().setup({model: detailModel})
new SeriesDropdown().setup({model: detailModel})
new CategoriesDropdown().setup({model: detailModel})

new SummaryView().setup({model: detailModel})
new RequireView().setup({model: detailModel})
new PlayVideoView().setup({model: detailModel})

let screenshotModel = new ScreenshotModel()
new Screenshots().setup({
    model: detailModel,
    screenshotModel: screenshotModel
})

let fileViewOld = new FileViewOld()
fileViewOld.setup({model: detailModel})

let addNewfile = new AddNewfile()
addNewfile.setup({model: detailModel})

let fileDeleteModel = new FileDeleteModel()
new Removefile().setup({
    model: detailModel,
    fileDeleteModel: fileDeleteModel
})

let DownloadCollection = Backbone.Collection.extend({model: DownloadModel})
new DownloadActionView().setup()

let filmDeleteModel = new FilmDeleteModel()
new Removefilm().setup({model: filmDeleteModel})

new SaveAndLoadModel().setup({
    model: detailModel,
    fileViewOld: fileViewOld
})

new DescriptionEditor().setup({model: detailModel})
new Releasefilm().setup({model: detailModel})
