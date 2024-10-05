let BannersDomain = Backbone.Model.extend({
    defaults: {
        action:              null,
        id:                  null,
        url:                 null,
        typeBanner:          null,
        bannerNewAdd:        {},
        allBannerAdditional: {},
    },

    url: function() {
        let action = encodeURIComponent(this.get('action'))
        let id     = this.get('id') ? encodeURIComponent(this.get('id')) : ''
        let url    = this.get('url') ? encodeURIComponent(this.get('url')) : ''

        const actionMap = {
            activate:     '/banners/activate-banner',
            removeSoftly: '/banners/banner-remove-softly',
            removeForced: '/banners/banner-remove-forced',
            save:         '/banners/banners-save',
        }

        return actionMap[action] + (id ? `?id=${id}` : '') + (url ? `&url=${url}` : '')
    },

    sync: function(method, model, options) {
        const action = this.get('action')
        if (action === 'removeSoftly' || action === 'removeForced') {
            options.type = 'DELETE'
        } else if (action === 'save' || action === 'activate') {
            options.type = 'POST'
        }

        return Backbone.sync(method, model, options)
    }
})

export { BannersDomain }
