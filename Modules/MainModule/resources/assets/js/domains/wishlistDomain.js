let WishlistDomain = Backbone.Model.extend({
    url: '/wishlist/toggle-wishlist',
    defaults: {
        game_id:        null,
        toggleWishlist: null
    }
})

export { WishlistDomain }
