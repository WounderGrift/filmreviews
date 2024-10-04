import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {UnloaderView} from "../../../../../public/js/helpers/unloader.js"

import {SeriesDomain as SeriesModel} from "./domains/seriesDomain.js"

let SeriesNameInit = Backbone.View.extend({
    setup: function (options) {
        this.model = options.model;
        this.initializeSeriesName();
    },

    initializeSeriesName: function () {
        this.model.set('seriesName', $('#series-name').val().trim());

        $(document).on('input', '#series-name', () => {
            this.model.set('seriesName', $('#series-name').val().trim());
        });
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
        this.model = options.model
        this.model.set('seriesId', $('.blog .container').data('code'))

        this.notSave = false
        this.loader  = $('#main-loader')

        let storedData = localStorage.getItem(this.model.get('seriesId'))
        if (storedData) {
            let data = JSON.parse(storedData)
            this.model.set(data)
            this.loadModel()
        } else {
            this.loader.remove()
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
        $("#series-name").val(this.model.get('seriesName'))

        let previewBox = $("#avatar")
        if (this.model.get('avatarPreview') !== "" && this.model.get('avatarPreview') !== "remove")
            previewBox.attr('src', this.model.get('avatarPreview'))
        else if (this.model.get('avatarPreview') === "remove")
            previewBox.attr('src', '/images/730.png')

        $('.text-show').html(this.model.get('description'))
    },

    saveModel: function () {
        if (this.notSave)
            return
        localStorage.setItem(this.model.get('seriesId'), JSON.stringify(this.model))
    },

    clearStorage: function () {
        this.notSave = true

        window.obUnloader.resetUnload()
        localStorage.removeItem(this.model.get('seriesId'))
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

        this.model.set('action', 'update')
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

            localStorage.removeItem(this.model.get('seriesId'))
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
new SaveAndLoadModel().setup({model: seriesModel})

new SeriesNameInit().setup({model: seriesModel})
new DescriptionEditor().setup({model: seriesModel})
new PreviewDetail().setup({model: seriesModel})

new SeriesRelease().setup({model: seriesModel});
