import {LoaderView} from "../helpers/loader.js"
import Swiper from 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.mjs'

let BannerSliderView = Backbone.View.extend({
    el: '.banner-slider',

    setup: function () {
        this.initSwiper()
        this.initSlides()
    },

    initSwiper: function () {
        let $swiperContainer = this.$('.swiper')

        if ($swiperContainer.children().length > 0) {
            let swiperInterval = $swiperContainer.data('interval')

            new Swiper($swiperContainer[0], {
                direction: 'horizontal',
                loop: false,
                autoplay: {
                    delay: swiperInterval,
                    disableOnInteraction: false,
                },
                scrollbar: {
                    el: '.swiper-scrollbar',
                },
            })
        }
    },

    initSlides: function() {
        $("#slider").responsiveSlides({
            auto: true,
            nav: false,
            speed: 500,
            namespace: "callbacks",
            pager: true,
        })
    }
})

let MobileMenu = Backbone.View.extend({
    el: 'span.menu',

    events: {
        'click': 'openMenu'
    },

    openMenu: function () {
        $("ul.nav1").slideToggle(300, function () {})
    }
})

let Back2Top = Backbone.View.extend({
    el: '#back2Top',

    events: {
        'click': 'handleTabClick',
    },

    setup: function () {
        $(window).on('scroll', this.handleScroll.bind(this))
    },

    handleScroll: function () {
        if ($(window).scrollTop() > 400)
            $('#back2Top').fadeIn()
        else
            $('#back2Top').fadeOut()
    },

    handleTabClick: function (event) {
        event.preventDefault()
        $("html, body").animate({scrollTop: 0}, "slow")
        return false
    }
})

let MaskContent = Backbone.View.extend({
    el: '.mask-content',

    events: {
        'click': 'maskToggle',
    },

    maskToggle: function () {
        $('#nav-toggle').prop('checked', function (evt, checked) {
            $('html').removeClass('hide-scroll')
            return !checked
        })
    }
})

let BannerJumpModel = Backbone.Model.extend({
    url: '/banners/jump'
})

let BannerJumpQuery = Backbone.View.extend({
    el: '.banner-jump',

    events: {
        'click': 'submitForm'
    },

    setup: function () {
        this.model = new BannerJumpModel()
        this.listenTo(this.model, 'sync', this.onSubmitSuccess)
    },

    submitForm: function (event) {
        event.preventDefault()

        let bannerId = this.$el.data('code')
        this.model.save({ id: bannerId }, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
    },

    onSubmitSuccess: function (model, response) {
        if (response.redirect_url)
            window.open(response.redirect_url, '_blank')
    }
})

let SkeletonLoader = Backbone.View.extend({
    el: window,

    setup: function() {
        $(window).on('load', this.onLoad, () => this.onLoad())
    },

    onLoad: function() {
        $('.games-skeleton-list').remove()
        $('.slider-skeleton').remove()
        $('.games-list').show()
        $('.slider-swiper').show()
        $('.info-block').show()
    }
})

let ClearStorage = Backbone.View.extend({
    el: window,

    setup: function() {
        $(window).on('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.keyCode === 82) {
                e.preventDefault()
                this.clearStorage()
            }
        })
    },

    clearStorage: function() {
        localStorage.clear()
        location.reload()
    }
})

new BannerSliderView().setup()
new BannerJumpQuery().setup()
new Back2Top().setup()
new SkeletonLoader().setup()
new ClearStorage().setup()

new MobileMenu()
new MaskContent()

new LoaderView()
