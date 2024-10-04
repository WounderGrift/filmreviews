let colorMenu = Backbone.View.extend({
    el: window,

    initialize: function() {
        this.menuItems   = $('.top-menu .nav1 li');
        this.activeIndex = 2;

        $(window).on('load', this.onLoad, () => this.onLoad());
    },

    onLoad: function() {
        if (this.activeIndex >= 0 && this.activeIndex < this.menuItems.length) {
            this.menuItems[this.activeIndex].classList.add('active');
        }
    }
});

let colorNewGames = new colorMenu();
