import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {BannersDomain as BannersModel} from "./domains/bannersDomain.js";

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
            return;
        this.isChooseSubmitting = true

        this.model.set('startDate', $(event.currentTarget).text())

        let that = this
        this.model.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.data) {
                    ['dataBanner', 'dataSponsors'].forEach(key => {
                        if (response.data[key].length) {
                            response.data[key].forEach(function (item) {
                                item.x = new Date(item.x + "T00:00:00");
                            });
                        }
                    });

                    if (response.data.dataBanner.length || response.data.dataSponsors.length) {
                        $('#chartContainer').show();
                        $('.corner-box-6').hide();
                        this.multipleAxes(response.data);
                    } else {
                        $('#chartContainer').hide();
                        $('.corner-box-6').show();
                    }

                    $('tr').each((index, item) => {
                        if (index === 0)
                            return;

                        let bannerId = $(item).data('banner-id');
                        if (bannerId) {
                            let jumpElement = $(item).find('#jump-banners');
                            jumpElement.text(response.data.jumpBanner[bannerId]);
                        } else {
                            let gameId = $(item).data('game-id');
                            let jumpElement = $(item).find('#jump-sponsors');
                            jumpElement.text(response.data.jumpSponsors[gameId]);
                        }
                    });
                }

                that.isChooseSubmitting = false;
            },
            error: (model, error) => {
                if (error && error.responseJSON && error.responseJSON.message)
                    new AlertView().errorWindowShow($('.error_profiles'), error.responseJSON.message);
                $('#main-loader').removeClass('show');
                that.isChooseSubmitting = false;
            }
        })
    },

    multipleAxes: function(dataChart) {
        let options = {
            exportEnabled: true,
            animationEnabled: true,
            title: {
                text: "Динамика заинтересованности в рекламе"
            },
            axisY: {
                title: "Переходы",
                titleFontColor: "#4D16ABFF",
                lineColor: "#4D16ABFF",
                labelFontColor: "#4D16ABFF",
                tickColor: "#4D16ABFF"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: this.toggleDataSeries
            },
            data: [{
                type: "spline",
                name: "Переходы на Баннеры",
                showInLegend: true,
                xValueFormatString: "MMM DD YYYY",
                yValueFormatString: "#,##0",
                lineColor: "purple",
                markerColor: "purple",
                dataPoints: dataChart.dataBanner
            }, {
                type: "spline",
                name: "Переходы на Спонсоров",
                showInLegend: true,
                xValueFormatString: "MMM DD YYYY",
                yValueFormatString: "#,##0",
                lineColor: "blueviolet",
                markerColor: "blueviolet",
                dataPoints: dataChart.dataSponsors
            }]
        };

        $("#chartContainer").CanvasJSChart(options);
        $('.canvasjs-chart-credit').hide();
    },

    toogleDataSeries: function(event) {
        event.dataSeries.visible = !(typeof (event.dataSeries.visible) === "undefined" || event.dataSeries.visible);
        event.chart.render();
    }
})

let bannersModel = new BannersModel();
new ChartView().setup({model: bannersModel})
