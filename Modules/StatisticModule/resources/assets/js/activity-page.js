import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {CommentDomain as CommentModel} from "./domains/commentDomain.js"
import {CommentDeleteDomain as CommentDeleteModel} from "./domains/commentDeleteDomain.js"
import {CommentResetDomain as CommentResetModel} from "./domains/commentResetDomain.js"
import {LikeDomain as LikeModel} from "./domains/likeDomain.js"
import {ActivityDomain as ActivityModel} from "./domains/activityDomain.js"

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

let CommentResetView = Backbone.View.extend({
    el: '.reset',

    events: {
        'click': 'resetComment'
    },

    setup: function(options) {
        this.model = options.model
        this.isFormSubmitting = false
    },

    resetComment: function(event) {
        event.preventDefault()

        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true

        let comment_id  = $(event.currentTarget).data('comment-id')

        this.model.set('id', comment_id)
        this.model.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            success: (model, response) => {
                if (response.refresh) {
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

let ChartView = Backbone.View.extend({
    setup: function(options) {
        this.model  = options.model
        this.isChooseSubmitting = false

        $('.get-data-profiles-chart').on('click', this.getDataCommentsChart.bind(this))

        if ($('#chartContainer').length) {
            $('.get-data-profiles-chart:contains("1МЕС")').click()
        }
    },

    getDataCommentsChart: function(event) {
        if (this.isChooseSubmitting)
            return
        this.isChooseSubmitting = true

        this.model.set('startDate', $(event.currentTarget).text())

        let that = this
        this.model.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.allValue) {
                    that.model.set({
                        downloads:    response.allValue.downloads,
                        commentaries: response.allValue.commentaries,
                        likesToGame:  response.allValue.likesToGame,
                        likesToComments: response.allValue.likesToComments,
                        wishlist:     response.allValue.wishlist,
                        newsletterUpdate: response.allValue.newsletterUpdate,
                        data: true
                    })
                }

                if (response.data) {
                    let showChart = response.data.downloads.length
                        || response.data.commentaries.length
                        || response.data.likesToGame.length
                        || response.data.likesToComments.length
                        || response.data.wishlist.length
                        || response.data.newsletterUpdate.length

                    ['downloads', 'commentaries', 'likesToGame',
                        'likesToComments', 'wishlist', 'newsletterUpdate'].forEach(key => {
                        if (response.data[key].length) {
                            response.data[key].forEach(function (item) {
                                item.x = new Date(item.x + "T00:00:00")
                            })
                        }
                    })

                    if (showChart) {
                        $('#chartContainer').show()
                        $('.corner-box-6').hide()
                        that.multipleAxes(response.data)
                    } else {
                        $('#chartContainer').hide()
                        $('.corner-box-6').show()
                    }
                } else {
                    $('#chartContainer').hide()
                    $('.corner-box-6').show()
                }

                that.isChooseSubmitting = false
            },
            error: (model, error) => {
                if (error && error.responseJSON && error.responseJSON.message)
                    new AlertView().errorWindowShow($('.error_comment'), error.responseJSON.message)
                $('#main-loader').removeClass('show')
                that.isChooseSubmitting = false
            }
        })
    },

    multipleAxes: function(dataChart) {
        let options = {
            animationEnabled: true,
            theme: "light2",
            title:{
                text: "Динамика Активностей",
            },
            axisY: {
                valueFormatString: "#0",
                includeZero: true,
                suffix: "",
                prefix: ""
            },
            legend: {
                cursor: "pointer",
                itemclick: this.toogleDataSeries
            },
            toolTip: {
                shared: true
            },
            data: [{
                type: "area",
                name: "Загрузки (" + this.model.get('downloads') + ")",
                markerSize: 5,
                showInLegend: true,
                xValueFormatString: "MMMM",
                yValueFormatString: "#0",
                dataPoints: dataChart.downloads
            }, {
                type: "area",
                name: "Комменты (" + this.model.get('commentaries') + ")",
                markerSize: 5,
                showInLegend: true,
                yValueFormatString: "#0",
                dataPoints: dataChart.commentaries
            }, {
                type: "area",
                name: "Лайки игр (" + this.model.get('likesToGame') + ")",
                markerSize: 5,
                showInLegend: true,
                yValueFormatString: "#0",
                dataPoints: dataChart.likesToGame
            }, {
                type: "area",
                name: "Лайки комментов (" + this.model.get('likesToComments') + ")",
                markerSize: 5,
                showInLegend: true,
                yValueFormatString: "#0",
                dataPoints: dataChart.likesToComments
            }, {
                type: "area",
                name: "Желаемые (" + this.model.get('wishlist') + ")",
                markerSize: 5,
                showInLegend: true,
                yValueFormatString: "#0",
                dataPoints: dataChart.wishlist
            }, {
                type: "area",
                name: "Подписки на обновления (" + this.model.get('newsletterUpdate') + ")",
                markerSize: 5,
                showInLegend: true,
                yValueFormatString: "#0",
                dataPoints: dataChart.newsletterUpdate
            }]
        }

        $("#chartContainer").CanvasJSChart(options)
        $('.canvasjs-chart-credit').hide()
    },

    toogleDataSeries: function(event) {
        event.dataSeries.visible = !(typeof (event.dataSeries.visible) === "undefined" || event.dataSeries.visible)
        event.chart.render()
    }
})

let FilterView = Backbone.View.extend({
    el: '#filter',

    events: {
        'submit': 'filterTable'
    },

    setup: function() {
        let path = window.location.pathname
        let regex = /\/all\/([^\/]*)/
        let matches = path.match(regex)

        if (matches && matches[1]) {
            let searchValue = decodeURIComponent(matches[1])
            this.$('input').val(searchValue)
        }
    },

    filterTable: function (event) {
        event.preventDefault()
        let searchValue = this.$('input').val().trim()
        window.location.href = `/chart/commentaries/${searchValue}`
    }
})

let commentModel = new CommentModel()
let LikeCollection = Backbone.Collection.extend({model: LikeModel})
new LikeActionView().setup({ model: commentModel })

let commentDeleteModel = new CommentDeleteModel()
new CommentRemoveView().setup({model: commentDeleteModel})

let commentResetModel = new CommentResetModel()
new CommentResetView().setup({model: commentResetModel})

let activityModel = new ActivityModel()
new ChartView().setup({model: activityModel})
new FilterView().setup()
