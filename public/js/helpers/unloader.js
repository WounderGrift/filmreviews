let UnloaderView = Backbone.View.extend({
    initialize: function() {
        this.unload();
        this.resetUnload();
        this.init();
    },

    unload(evt) {
        let message = "Данные не будут сохранены после обновления страницы или перехода";
        if ($("#taskname").val() !== "" || $(".note-editable").text()) {
            if (typeof evt == "undefined") {
                evt = window.event;
            }

            if (evt) {
                evt.returnValue = message;
            }

            return message;
        }
    },

    resetUnload() {
        $(window).off('beforeunload', this.unload);

        $(document).on('click', () => {
            $(window).on('beforeunload', this.unload);
        });
    },

    init() {
        $(document).on('click', () => {
            $(window).on('beforeunload', this.unload);
        });

        $('a#ms-submit-button').on('click', () => {
            $(window).off('beforeunload', this.unload);

            $(document).on('click', () => {
                $(window).on('beforeunload', this.unload);
            });
        });

        $(document).on('submit', 'form', () => {
            this.resetUnload();
        });

        $(document).on('keydown', (event) => {
            if ((event.ctrlKey && event.keyCode === 116) || event.keyCode === 116) {
                this.resetUnload();
            }
        });
    }
});

export {UnloaderView};
