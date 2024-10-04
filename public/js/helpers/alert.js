let AlertView = Backbone.View.extend({
    errorWindowShow: function(element, message) {
        $(element).text(message)
        $(element).css('background', 'var(--pink)')
        $(element).addClass('show')

        setTimeout(function() {
            $(element).removeClass('show')
        }, 3000)
    },

    successWindowShow: function(element, message) {
        $(element).text(message)
        $(element).css('background', 'var(--green)')
        $(element).addClass('show')

        setTimeout(function() {
            $(element).removeClass('show')
        }, 3000)
    },

    startTimer: function(button, window, text) {
        const endTime = new Date()
        endTime.setMinutes(endTime.getMinutes() + 1)

        this.successWindowShow(window, text)
        const updateTimer = () => {
            const now = new Date()
            const timeRemaining = endTime - now

            if (timeRemaining <= 0) {
                $(button).text('Отправить еще раз').prop('disabled', false)
            } else {
                const minutes = Math.floor(timeRemaining / 60000)
                const seconds = Math.floor((timeRemaining % 60000) / 1000)


                const formattedMinutes = String(minutes).padStart(2, '0')
                const formattedSeconds = String(seconds).padStart(2, '0')

                $(button).text(`${formattedMinutes}:${formattedSeconds}`)

                setTimeout(updateTimer, 1000)
            }
        }

        updateTimer()
    }
})

export {AlertView}
