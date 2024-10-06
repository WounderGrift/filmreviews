import {AlertView}  from "../../../../../public/js/helpers/alert.js"

import {CommentDomain as CommentModel} from "./domains/commentDomain.js"
import {CommentDeleteDomain as CommentDeleteModel} from "./domains/commentDeleteDomain.js"
import {LikeDomain as LikeModel} from "./domains/likeDomain.js"
import {ReportDomain as ReportModel} from "./domains/reportDomain.js"
import {DownloadDomain as DownloadModel} from "./domains/downloadDomain.js"
import {SubscriptionDomain as SubscriptionModel} from "./domains/subscriptionDomain.js"

let InputCommentView = Backbone.View.extend({
    el: '#response-form',

    events: {
        'input #comment-textarea': 'handleInputChange',
        'submit': 'handleFormSubmit'
    },

    setup: function(options) {
        this.model  = options.model
        this.submitButton = this.$('[type="submit"]')
        this.$counter     = this.$('#counter')
        this.loader = $('#main-loader')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    handleInputChange: function(event) {
        let $input = $(event.currentTarget)
        let attributeName = $input.attr('name')
        this.model.set(attributeName, $input.val())

        this.$counter.text(`150 / ${$input.val().length}`)
    },

    handleFormSubmit: function(event) {
        event.preventDefault()
        this.loader.addClass('show')

        if (this.submitButton.prop('disabled'))
            return
        this.submitButton.prop('disabled', true)

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.success) {
            new AlertView().successWindowShow($('.error_comment'), 'Комментарий добавлен')
            this.loader.removeClass('show')
            location.reload()
        }
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message) {
            new AlertView().errorWindowShow($('.error_comment'), error.responseJSON.message)
        }
        this.loader.removeClass('show')
        this.submitButton.prop('disabled', false)
    }
})

let LikeActionView = Backbone.View.extend({
    el: '.like-action input',

    events: {
        'change': 'toggleLike'
    },

    setup: function(options) {
        this.model = options.model
        this.likeQueue = new LikeCollection()
        this.debounceLikeTimeout = null
    },

    toggleLike: function(event) {
        let like  = $(event.currentTarget)
        let count = like.closest('.like-action').siblings('.favorite-count')
        let toggleLike  = like.is(":checked")
        let comment_id  = like.data('comment-id') ?? null

        let errorWindows = !!comment_id ? $('.error_comment') : $('.error_subscribe')

        let existingItem = this.likeQueue.findWhere({
            game_id: this.model.get('game_id'),
            comment_id: comment_id
        })

        if (existingItem) {
            existingItem.set('toggleLike', toggleLike)
        } else {
            this.likeQueue.add({
                toggleLike: toggleLike,
                game_id: this.model.get('game_id'),
                comment_id: comment_id,
                errorWindows: errorWindows
            })
        }

        if (count.length > 0) {
            let currentValue = parseInt(count.text())
            if (!isNaN(currentValue)) {
                if (toggleLike) {
                    currentValue++
                    count.text(currentValue)
                } else {
                    currentValue--
                    count.text(currentValue)
                }
            }
        }

        clearTimeout(this.debounceLikeTimeout)
        this.debounceLikeTimeout = setTimeout(() => {
            this.processLikeQueue()
        }, 300)
    },

    processLikeQueue: async function() {
        for (let item of this.likeQueue.models) {
            await this.processItem(item)
            await this.delay(300)
        }
    },

    processItem: function(item) {
        return new Promise((resolve, reject) => {
            item.save(null, {
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: () => {
                    this.likeQueue.remove(item)
                    resolve(true)
                },
                error: (model, error) => {
                    if (error?.responseJSON?.message)
                        new AlertView().errorWindowShow(item.get('errorWindows'), error.responseJSON.message)

                    this.likeQueue.remove(item)
                    reject(error.responseJSON.message)
                }
            })
        })
    },

    delay: function(ms) {
        return new Promise(resolve => setTimeout(resolve, ms))
    }
})

let ReplyCommentView = Backbone.View.extend({
    el: '.reply',

    events: {
        'click': 'toggleReply'
    },

    setup: function(options) {
        this.model = options.model
        this.blockquote   = $('#comment-reply')
        this.cancelButton = $('#cancel-reply')
    },

    toggleReply: function(event) {
        $(document).on('click', '#cancel-reply', this.cancelReply.bind(this))

        let originalComment = $(event.currentTarget).closest('.media-body').find('p').text()
        this.model.set('quote', originalComment)
        this.model.set('whom_id', $(event.currentTarget).data('comment-id'))

        this.blockquote.text(originalComment)
        this.blockquote.css('display', 'block')
        this.cancelButton.css('display', 'block')
    },

    cancelReply: function(event) {
        let target = $(event.target)
        if (target.is('#cancel-reply')) {
            this.model.set('quote', null)
            this.model.set('whom_id', null)

            this.blockquote.text('')
            this.blockquote.css('display', 'none')
            this.cancelButton.css('display', 'none')
        }
    }
})

let CommentRemoveView = Backbone.View.extend({
    el: '.media-body',

    events: {
        'click .remove': 'removeComment'
    },

    setup: function(options) {
        this.model = options.model
        this.isFormSubmitting = false
    },

    removeComment: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true

        let comment_id  = $(event.currentTarget).data('comment-id')
        let hard_delete = $(event.currentTarget).data('hard')

        this.model.set('id', comment_id)
        this.model.set('hard', hard_delete)

        this.model.destroy({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    if (hard_delete)
                        new AlertView().successWindowShow($('.error_comment'), 'Комментарий удален навсегда')
                    else
                        new AlertView().successWindowShow($('.error_comment'), 'Комментарий удален')
                    location.reload()
                }
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_comment'), error.responseJSON.message)
                this.isFormSubmitting = false
            }
        })
    }
})

let SendReportFormView = Backbone.View.extend({
    el: '#report-error',

    events: {
        'click': 'handleClick'
    },

    handleClick: function() {
        $('.popup-send-error').removeClass('disabled')
        $('.popup-content.send-error').removeClass('disabled')

        $('html').addClass('hide-scroll')
        $('.container').addClass('mrg-container')
    }
})

let SendReportView = Backbone.View.extend({
    el: '#send-error-form',

    events: {
        'input #send-error-text': 'handleInputChange',
        'submit': 'handleFormSubmit'
    },

    setup: function(options) {
        this.model = options.model
        this.submitButton = this.$('[type="submit"]')
        this.$counter     = this.$('#counter_report')

        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
        this.listenTo(this.model, 'error', this.onSubmitError)
    },

    handleInputChange: function(event) {
        let $input = $(event.currentTarget)
        let attributeName = $input.attr('name')
        this.model.set(attributeName, $input.val())

        this.$counter.text(`150 / ${$input.val().length}`)
    },

    handleFormSubmit: function(event) {
        event.preventDefault()

        if (this.submitButton.prop('disabled'))
            return
        $('.restore .loader').addClass('show')

        this.model.set('text', $('#send-error-text').val())

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.success) {
            new AlertView().successWindowShow($('.error-reporter'), 'Жалоба отправлена')
            location.reload()
        }
    },

    onSubmitError: function (model, error) {
        if (error?.responseJSON?.message) {
            new AlertView().errorWindowShow($('.error-reporter'), error.responseJSON.message)
        }
        this.submitButton.prop('disabled', false)
    }
})

let SpoilerHeadView = Backbone.View.extend({
    el: '.spoiler-header',

    events: {
        'click': 'openSpoiler'
    },

    setup: function() {
        this.spoiler = $('.spoiler')
        $(window).on('resize', this.resizeSpoiler.bind(this))
    },

    openSpoiler: function(event) {
        if (!$(event.target).hasClass('download')) {
            let spoiler = $(event.currentTarget).closest('.spoiler')
            spoiler.toggleClass('open')

            let spoilerContent = spoiler.find('.spoiler-content')
            let maxHeight = spoiler.hasClass('open') ? spoilerContent.prop('scrollHeight') + 'px' : '0'
            spoilerContent.css('max-height', maxHeight)
        }
    },

    resizeSpoiler: function() {
        this.spoiler.each(function() {
            let thisSpoiler = $(this)
            let spoilerContent = thisSpoiler.find('.spoiler-content')
            let maxHeight = thisSpoiler.hasClass('open') ? spoilerContent.prop('scrollHeight') + 'px' : '0'
            spoilerContent.css('max-height', maxHeight)
        })
    }
})

let PlayVideoView = Backbone.View.extend({
    el: '#playButton',

    events: {
        'click': 'play'
    },

    play: function() {
        let trailer = $('#videoContainer').data('trailer') + "?autoplay=1&mute=1&rel=0&showinfo=0&iv_load_policy=3"
        $("#videoContainer").html(`
            <div class="video-responsive">
                <iframe width="560" height="315" src="${trailer}"
                frameborder="0" allowfullscreen></iframe>
            </div>
        `)
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
        this.loader = $('#main-loader')
    },

    download: function(event) {
        this.loader.addClass('show')

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

                    this.loader.removeClass('show')
                    this.downloadQueue.remove(item)
                    resolve(true)
                },
                error: (model, error) => {
                    if (error?.responseJSON?.message)
                        new AlertView().errorWindowShow(item.get('errorWindows'), error.responseJSON.message)

                    this.loader.removeClass('show')
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

let SubscriptionView = Backbone.View.extend({
    setup: function(options) {
        this.model = options.model
        this.loader = $('#main-loader')

        $('.user-subscribe').on('click', this.subscribe.bind(this))
        $('.user-unsubscribe').on('click', this.unsubscribe.bind(this))
        $('.button-subscribe').on('click', this.showModalForAnon.bind(this))
        $('#subscribe-form').on('submit', this.subscribeAnonSubmit.bind(this))
    },

    subscribe: function(event) {
        event.preventDefault()
        this.loader.addClass('show')

        if (this.model.get('isUserSubscribe'))
            return
        this.model.set('isUserSubscribe', true)

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    new AlertView().successWindowShow($('.error_subscribe'), 'Подписка оформлена')
                    this.loader.removeClass('show')
                    location.reload()
                }
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_subscribe'), error.responseJSON.message)
                this.model.set('isUserSubscribe', false)
                this.loader.removeClass('show')
            }
        })
    },

    unsubscribe: function(event) {
        event.preventDefault()
        this.loader.addClass('show')

        if (this.model.get('isUserUnsubscribe'))
            return
        this.model.set('isUserUnsubscribe', true)

        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    new AlertView().successWindowShow($('.error_subscribe'), 'Отписка выполнена успешно')
                    this.loader.removeClass('show')
                    location.reload()
                }
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error_subscribe'), error.responseJSON.message)
                this.model.set('isUserSubscribe', false)
                this.loader.removeClass('show')
            }
        })
    },

    showModalForAnon: function() {
        $('.popup-subscribe').removeClass('disabled')
        $('.popup-content.subscribe').removeClass('disabled')

        $('html').addClass('hide-scroll')
        $('.container').addClass('mrg-container')
    },

    subscribeAnonSubmit: function(event) {
        event.preventDefault()
        this.loader.addClass('show')

        if (this.model.get('isAnonSubscribe'))
            return
        this.model.set('isAnonSubscribe', true)

        $('.restore .loader').addClass('show')

        this.model.set('email', $('#subscribe-email').val())
        this.model.save(null, {
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.success) {
                    new AlertView().successWindowShow($('.error-subscribe-popup'), 'Подписка оформлена')
                    this.loader.removeClass('show')
                    location.reload()
                }
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error-subscribe-popup'), error.responseJSON.message)
                this.model.set('isAnonSubscribe', false)
                this.loader.removeClass('show')
            }
        })
    }
})

let ThankYouButtonView = Backbone.View.extend({
    el: "#thankYouButton",

    setup: function() {
        $(window).on("scroll", this.checkScrollPosition.bind(this))
        this.downloadContainer = $('.download-container')
    },

    checkScrollPosition: function() {
        let windowHeight  = $(window).height()
        let currentScroll = $(window).scrollTop()

        if (this.downloadContainer.length) {
            let elementOffsetTop = this.downloadContainer.offset().top

            if (currentScroll + windowHeight > elementOffsetTop && currentScroll < elementOffsetTop) {
                this.$el.removeClass("hide-anim")
                this.$el.addClass("show-anim")
            }
        }
    }
})

let gameId = $('main .container').data('game-id')

let commentModel = new CommentModel()
commentModel.set('game_id', gameId)
new InputCommentView().setup({ model: commentModel })
new ReplyCommentView().setup({ model: commentModel })

let LikeCollection = Backbone.Collection.extend({model: LikeModel})
new LikeActionView().setup({ model: commentModel })

let commentDeleteModel = new CommentDeleteModel()
new CommentRemoveView().setup({model: commentDeleteModel})

let reportModel = new ReportModel()
reportModel.set('game_id', gameId)
new SendReportFormView()
new SendReportView().setup({ model: reportModel })

new SpoilerHeadView().setup()
new PlayVideoView()

let DownloadCollection = Backbone.Collection.extend({model: DownloadModel})
new DownloadActionView().setup()

let subscriptionModel = new SubscriptionModel()
subscriptionModel.set('game_id', gameId)
new SubscriptionView().setup({model: subscriptionModel})
new ThankYouButtonView().setup()
