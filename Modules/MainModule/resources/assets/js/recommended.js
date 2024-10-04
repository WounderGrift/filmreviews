let RecommendedPopulate = Backbone.View.extend({
    el: "#flexiselDemo1",

    initialize: function (options) {
        this.options = _.extend({
            visibleItems: 4,
            animationSpeed: 1000,
            autoPlay: true,
            autoPlaySpeed: 3000,
            pauseOnHover: true,
            navigationTargetSelector: ".navigator-flexisel",
            enableResponsiveBreakpoints: true,
            responsiveBreakpoints: {
                portrait: {
                    changePoint: 480,
                    visibleItems: 1
                },
                landscape: {
                    changePoint: 640,
                    visibleItems: 2
                },
                tablet: {
                    changePoint: 768,
                    visibleItems: 3
                }
            }
        }, options || {});

        this.initFlexisel();
    },

    initFlexisel: function () {
        $("#flexiselDemo1").flexisel(this.options);
    }
});

let RecommendedLastUpdate = Backbone.View.extend({
    el: "#flexiselDemo2",

    initialize: function (options) {
        this.options = _.extend({
            visibleItems: 4,
            animationSpeed: 1000,
            autoPlay: true,
            autoPlaySpeed: 3000,
            pauseOnHover: true,
            navigationTargetSelector: ".navigator-flexisel",
            enableResponsiveBreakpoints: true,
            responsiveBreakpoints: {
                portrait: {
                    changePoint: 480,
                    visibleItems: 1
                },
                landscape: {
                    changePoint: 640,
                    visibleItems: 2
                },
                tablet: {
                    changePoint: 768,
                    visibleItems: 3
                }
            }
        }, options || {});

        this.initFlexisel();
    },

    initFlexisel: function () {
        $("#flexiselDemo2").flexisel(this.options);
    }
});

let ImagesLastUpdateView = Backbone.View.extend({
    el: '.last-update img',

    initialize: function () {
        this.$el.height(125);
    }
});

let PlayButtonView = Backbone.View.extend({
    el: '#playButton',

    events: {
        'click': 'playTrailer'
    },

    playTrailer: function () {
        let trailer = $('#videoContainer').data('trailer') + "?autoplay=1&mute=1&rel=0&showinfo=0&iv_load_policy=3";
        $("#videoContainer").html(`
            <div class="video-responsive">
                <iframe width="560" height="315" src="${trailer}"
                frameborder="0" allowfullscreen></iframe>
            </div>
        `);
    }
});

let SkeletonLoader = Backbone.View.extend({
    el: window,

    initialize: function() {
        $(window).on('load', this.onLoad, () => this.onLoad());
    },

    onLoad: function() {
        $('.popular-and-recommended, .poster, .last-release, .trailer, .last-update').show();
        $('.skeleton').remove();
    }
});

let recommendedLastUpdateView = new RecommendedLastUpdate();

let recommendedPopulate  = new RecommendedPopulate();
let imagesLastUpdateView = new ImagesLastUpdateView();
let playButtonView       = new PlayButtonView();

let skeletonLoaderRecommended = new SkeletonLoader();
