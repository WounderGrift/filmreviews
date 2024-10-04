let SubscriptionDomain = Backbone.Model.extend({
    url: function() {
        if (this.get('isUserSubscribe') || this.get('isAnonSubscribe')) {
            return '/detail/subscribe';
        } else {
            return '/detail/unsubscribe';
        }
    },

    defaults: {
        game_id:           null,
        email:             null,
        isUserSubscribe:   false,
        isUserUnsubscribe: false,
        isAnonSubscribe:   false,
    },
})

export { SubscriptionDomain };
