import {AlertView} from "../../../../../public/js/helpers/alert.js"
import {FilesRecyclebinDomain as FilesRecyclebinModel} from "./domains/filesRecyclebinDomain.js"
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

let RecyclebinFilesView = Backbone.View.extend({
    setup: function(options) {
        this.model  = options.model
        this.isFormSubmitting = false
        this.loader = $('#main-loader')

        this.spoiler = $('.spoiler-description')
        this.spoiler.on('click', '.file-container .download-count .restore-file',
            this.removeSoftlyFile.bind(this))
        this.spoiler.on('click', '.file-container .download-count .remove-file-force',
            this.removeForcedFile.bind(this))
        $('#empty-trash').on('click', this.clearRecycleBin.bind(this))
    },

    removeSoftlyFile: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        let fileContainer = $(event.currentTarget).closest(".file-container")

        this.model.set('action', 'removeSoftly')
        this.model.set('id', fileContainer.data('id'))

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    this.loader.removeClass('show')
                    fileContainer.remove()
                }

                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_trashed_files'), error.responseJSON.message)
                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    },

    removeForcedFile: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true
        this.loader.addClass('show')

        let fileContainer = $(event.currentTarget).closest(".file-container")

        this.model.set('action', 'removeForced')
        this.model.set('id', fileContainer.data('id'))

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    this.loader.removeClass('show')
                    fileContainer.remove()
                }

                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_trashed_files'), error.responseJSON.message)
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
                    new AlertView().errorWindowShow($('.error_trashed_files'), error.responseJSON.message)
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
        let torrent_id   = download.data('code')

        let existingItem = this.downloadQueue.findWhere({
            torrent_id: torrent_id
        })

        if (existingItem) {
            return false
        } else {
            this.downloadQueue.add({
                torrent_id: torrent_id,
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

let filesRecyclebinModel = new FilesRecyclebinModel()
new SpoilerHeadView().setup()

new RecyclebinFilesView().setup({model: filesRecyclebinModel})

let DownloadCollection = Backbone.Collection.extend({model: DownloadModel})
new DownloadActionView().setup()
