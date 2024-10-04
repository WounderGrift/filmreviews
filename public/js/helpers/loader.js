let LoaderView = Backbone.View.extend({
    initialize: function () {
        this.loader = $('#main-loader');
        this.loader.removeClass('show');
    }
})

export { LoaderView };
