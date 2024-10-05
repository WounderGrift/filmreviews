let colorMenu = Backbone.View.extend({
    el: window,

    initialize: function() {
        $(window).on('load', this.onLoad, () => this.onLoad())
    },

    onLoad: function() {
        $('.logo h1 a').css('color', '#ff7e00')
    }
})

let colorMain = new colorMenu()
