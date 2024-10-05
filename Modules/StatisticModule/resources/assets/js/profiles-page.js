import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {ProfilesDomain as ProfilesModel} from "./domains/profilesDomain.js"

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
                if (response.data) {
                    this.model.set('allUsers', response.allValues)

                    response.data.forEach(function(item) {
                        item.x = new Date(item.x + "T00:00:00")
                    })

                    if(response.data.length) {
                        $('#chartContainer').show()
                        $('.corner-box-6').hide()
                        this.multipleAxes(response.data)
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
                    new AlertView().errorWindowShow($('.error_profiles'), error.responseJSON.message)
                $('#main-loader').removeClass('show')
                that.isChooseSubmitting = false
            }
        })
    },

    multipleAxes: function(dataChart) {
        let options = {
            exportEnabled: true,
            animationEnabled: true,
            title: {
                text: "Динамика пользователей"
            },
            axisY: {
                title: "Зарегистрировано",
                titleFontColor: "#4F81BC",
                lineColor: "#4F81BC",
                labelFontColor: "#4F81BC",
                tickColor: "#4F81BC"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: this.toogleDataSeries
            },
            data: [{
                type: "spline",
                name: "Пользователи (" + this.model.get('allUsers') + ")",
                showInLegend: true,
                xValueFormatString: "MMM DD YYYY",
                yValueFormatString: "#,##0",
                dataPoints: dataChart
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

let profilesModel = new ProfilesModel()
new ChartView().setup({model: profilesModel})
new FilterView().setup()
