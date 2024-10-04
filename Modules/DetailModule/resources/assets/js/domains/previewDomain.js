let PreviewDomain = Backbone.Model.extend({
    url: function() {
        const actionMap = {
            setExisted:    '/detail/preview-set-existed',
            removeExisted: '/detail/preview-remove-existed',
        };

        return actionMap[this.get('action')];
    },

    defaults: {
        fileName:    '',
        oldUri:      '',
        action:      null,
        gameId:      null,
        that:        null,
        whatPreview: null
    },

    initialize: function(attributes, options) {
        if (options && options.gameId) {
            this.set('gameId', options.gameId);
        }
    },

    setPreviewData: function(that, whatPreview) {
        this.set({
            fileName:    that.closest('label').text().trim(),
            oldUri:      that.closest('label').data('uri'),
            that:        that,
            whatPreview: whatPreview
        });
    }
});

export { PreviewDomain };