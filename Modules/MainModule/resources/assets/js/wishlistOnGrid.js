import { WishlistDomain as WishlistModel } from "./domains/wishlistDomain.js"

let WishlistActionView = Backbone.View.extend({
    el: '.wishlist-action input',

    events: {
        'change': 'toggleWishlist'
    },

    setup: function() {
        this.wishQueue = new WishlistCollection()
        this.debounceWishlistTimeout = null
    },

    toggleWishlist: function(event) {
        let wishlist = $(event.currentTarget)
        let toggleWishlist = wishlist.is(":checked")
        let game_id  = $('main .container').data('game-id') ?? wishlist.data('game-id')
        let count    = $('.wishlist.favorite-count')

        let existingItem = this.wishQueue.findWhere({
            game_id: game_id
        })

        if (existingItem) {
            existingItem.set('toggleWishlist', toggleWishlist)
        } else {
            this.wishQueue.add({
                toggleWishlist: toggleWishlist,
                game_id: game_id
            })
        }

        if (count.length > 0) {
            let currentValue = parseInt(count.text(), 10) // Указание системы счисления (10)

            if (!isNaN(currentValue)) {
                if (toggleWishlist) {
                    currentValue++
                    count.text(currentValue)

                    let subscribeBtn = $('.user-subscribe')
                    if (subscribeBtn.text().trim() === 'Подписаться на обновления') {
                        subscribeBtn.removeClass('user-subscribe')
                            .addClass('user-unsubscribe')
                            .text('Отписаться от новостей')

                        let newsletterCount = $('.newsletter_count')
                        let newsletterValue = parseInt(newsletterCount.text(), 10) + 1
                        newsletterCount.text(newsletterValue)
                    }
                } else {
                    currentValue--
                    count.text(currentValue)
                }
            }
        }

        clearTimeout(this.debounceWishlistTimeout)
        this.debounceWishlistTimeout = setTimeout(() => {
            this.processWishlistQueue()
        }, 300)
    },

    processWishlistQueue: async function() {
        for (let item of this.wishQueue.models) {
            await this.processItem(item)
            await this.delay(300)
        }
    },

    processItem: function(item) {
        return new Promise((resolve, reject) => { // Использование стрелочной функции для сохранения контекста
            item.save(null, {
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: () => {
                    this.wishQueue.remove(item)
                    resolve()
                },
                error: () => {
                    this.wishQueue.remove(item)
                    reject()
                }
            })
        })
    },

    delay: function(ms) {
        return new Promise(resolve => setTimeout(resolve, ms))
    }
})

let WishlistCollection = Backbone.Collection.extend({model: WishlistModel})
new WishlistActionView().setup()
