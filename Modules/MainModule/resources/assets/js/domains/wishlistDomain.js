let WishlistDomain = Backbone.Model.extend({
    url: '/wishlist/toggle-wishlist',
    defaults: {
        film_id:        null,
        toggleWishlist: null
    }
})

export { WishlistDomain }
