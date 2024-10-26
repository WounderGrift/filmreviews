import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {UnloaderView} from "../../../../../public/js/helpers/unloader.js"

import {SeriesDomain as SeriesModel} from "./domains/seriesDomain.js"

let SeriesNameInit = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model
        this.initializeSeriesName()
    },

    initializeSeriesName: function () {
        this.model.set('seriesName', $('#series-name').val().trim())
        this.model.set('description', $('.text-show').html())

        $(document).on('input', '#series-name', () => {
            this.model.set('seriesName', $('#series-name').val().trim())
        })
    }
})

let PreviewDetail = Backbone.View.extend({
    setup: function(options) {
        this.model = options.model
        this.previewDetailInput = $(".summary-block #detailPreviewInput")
        this.previewDetailInput.on("change", this.changePreviewAvatar.bind(this))
        $(".summary-block #avatar-remove").on("click", this.removePreviewAvatar.bind(this))
    },

    changePreviewAvatar: function() {
        let fileInput = this.previewDetailInput[0]
        let file      = fileInput.files[0]
        let reader    = new FileReader()
        let ava       = $(".summary-block #avatar")

        let that = this
        reader.onload = function (event) {
            $('.summary-block #avatar-name').text(file.name)
            ava.attr("src", event.target.result)
            that.model.set("avatarPreview", event.target.result)
        }

        reader.readAsDataURL(file)
    },

    removePreviewAvatar: function(event) {
        event.preventDefault()
        $(".summary-block #avatar").attr('src', 'https://via.placeholder.com/694x180')
        $(".summary-block #file").val("")
        $(".summary-block #avatar-name").text("Обложка не выбрана")
        this.model.set("avatarPreview", "remove")
    }
})

let CtrlV = Backbone.View.extend({
    setup: function(options) {
        this.model = options.model
        this.dropArea = null

        $('.poster-box #avatar').click((event) => {
            this.dropArea = $(event.currentTarget).data('target')
        })

        $(document).on('click', (event) => {
            let clickedElement = event.target
            if (!$(clickedElement).is('#avatar')) {
                this.dropArea = null
            }
        })

        $(document).on('paste', (event) => {
            let items = (event.originalEvent.clipboardData || event.clipboardData).items

            if (this.dropArea) {
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        let file = items[i].getAsFile()

                        if (this.dropArea === 'series')
                            this.setPreviewDetailFromCtrlV(file)
                        break
                    }
                }
            }
        })
    },

    setPreviewDetailFromCtrlV: function(file) {
        let reader = new FileReader()
        let ava = $(".poster-box #avatar")

        reader.onload = (event) => {
            $('.poster-box #avatar-name').text(file.name)
            ava.attr("src", event.target.result)
            this.model.set('avatarPreview', event.target.result)
        }

        reader.readAsDataURL(file)
    }
})

let DescriptionEditor = Backbone.View.extend({
    setup: function(options) {
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

let SaveAndLoadModel = Backbone.View.extend({
    setup: function (options) {
        this.model   = options.model
        this.notSave = false
        this.loader  = $('#main-loader')

        let storedData = localStorage.getItem('saveNewSeries')
        if (storedData) {
            let data = JSON.parse(storedData)
            this.model.set(data)
            this.loadModel()
        } else {
            this.loader.removeClass('show')
            $('#loading-model').remove()
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
        this.loader.show()
        $("#series-name").val(this.model.get('seriesName'))

        let previewBox = $("#avatar")
        if (this.model.get('avatarPreview') !== "" && this.model.get('avatarPreview') !== null)
            previewBox.attr('src', this.model.get('avatarPreview'))

        $('.text-show').html(this.model.get('description'))
        this.loader.removeClass('show')
    },

    saveModel: function () {
        if (this.notSave)
            return
        localStorage.setItem('saveNewSeries', JSON.stringify(this.model))
    },

    clearStorage: function () {
        this.notSave = true

        window.obUnloader.resetUnload()
        localStorage.removeItem('saveNewSeries')
        location.reload()
    }
})

let SeriesRelease = Backbone.View.extend({
    el: '#save-detail',

    events: {
        'click': 'release'
    },

    setup: function(options) {
        this.model  = options.model
        this.isFormSubmitting = false
        this.loader = $('#main-loader')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    release: function() {
        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        this.model.set('action', 'create')
        this.model.save(null, {
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
            localStorage.removeItem('saveNewSeries')
            window.location.href = response.redirect_url
        }

        this.loader.removeClass('show')
        this.isFormSubmitting = false
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message)
            new AlertView().errorWindowShow($('.error_series'), error.responseJSON.message)
        this.loader.removeClass('show')
        this.isFormSubmitting = false
    }
})

let seriesModel = new SeriesModel()
new SeriesNameInit().setup({model: seriesModel})
new SaveAndLoadModel().setup({model: seriesModel})

new DescriptionEditor().setup({model: seriesModel})
new PreviewDetail().setup({model: seriesModel})
new CtrlV().setup({model: seriesModel})

new SeriesRelease().setup({model: seriesModel})
