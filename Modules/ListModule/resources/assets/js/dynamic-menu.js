import {AlertView} from "../../../../../public/js/helpers/alert.js"

import {DynamicDomain as DynamicModel} from "./domains/dynamicDomain.js"

let DynamicMenu = Backbone.View.extend({
    initialize(options) {
        this.model  = options.model
        this.isFormSubmitting = false
        this.loader = $('#main-loader')

        this.categoriesRemove = []
        this.controllerFieldsCategory()

        this.repackRemove = []
        this.controllerFieldsRepack()

        this.categoriesObject = {}
        this.repacksObject = {}

        this.saveBound = this.save.bind(this)

        $('#save').on('click', this.saveBound)
    },

    controllerFieldsCategory: function() {
        let Mathrand = Math.random()
        let templateCategory = '<li class="requirement-edit category">' +
            '<input id="category-label" type="text" class="category-label detail-summary-input" value="">' +
            '<input id="category-url" type="text" class="category-url detail-summary-input" value="">' +
            '<label class="checkbox-container soft-checkbox" for="is_soft_' + Mathrand + '" style="margin-left: 5px margin-top: 11px">\n' +
            'Софт\n' +
            '<input type="checkbox" id="is_soft_' + Mathrand + '" value="1">' +
            '<span class="checkmark"></span>\n' +
            '</label>' +
            '<i class="fas fa-times fa-lg remove-category remove" style="padding: 15px"></i>' +
            '</li>'

        $('#add-category').on('click', function () {
            $(".requirements-container .categories .requirement-list").append(templateCategory)
        })

        let that = this
        $(document).on('click', '.remove-category', function() {
            let removeParent = $(this).closest('.category')

            if (!removeParent.hasClass('deleted')) {
                that.categoriesRemove.push(removeParent.data('code'))
                that.model.set('categoriesRemove', that.categoriesRemove)
                removeParent.addClass('deleted')
            } else {
                let code = removeParent.data('code')
                let index = that.categoriesRemove.indexOf(code)

                if (index > -1)
                    that.categoriesRemove.splice(index, 1)

                that.model.set('categoriesRemove', that.categoriesRemove)
                removeParent.removeClass('deleted')
            }
        })
    },

    controllerFieldsRepack: function() {
        let templateRepack = '<li class="requirement-edit repack">' +
            '<input id="repack-label" type="text" class="repack-label detail-summary-input" value="">' +
            '<input id="repack-url" type="text" class="repack-url detail-summary-input" value="">' +
            '<i class="fas fa-times fa-lg remove-repack remove" style="padding: 15px"></i>' +
            '</li>'

        $('#add-repack').on('click', function () {
            $(".requirements-container .repacks .requirement-list").append(templateRepack)
        })

        let that = this
        $(document).on('click', '.remove-repack', function() {
            let removeParent = $(this).closest('.repack')

            if (!removeParent.hasClass('deleted')) {
                that.repackRemove.push(removeParent.data('code'))
                console.log(that.repackRemove)
                that.model.set('repacksRemove', that.repackRemove)
                removeParent.addClass('deleted')
            } else {
                let code = removeParent.data('code')
                let index = that.repackRemove.indexOf(code)

                if (index > -1)
                    that.repackRemove.splice(index, 1)

                that.model.set('repacksRemove', that.repackRemove)
                removeParent.removeClass('deleted')
            }
        })
    },

    getAllCategories: function () {
        let categories = $('.requirements-container .category:not(.deleted)')

        let that = this
        categories.each(function() {
            let key  = $(this).find('.category-label').val()
            let url  = $(this).find('.category-url').val()
            let soft = $(this).find('input[type="checkbox"]').is(':checked')
            let code = $(this).data('code')

            if (key && url)
                that.categoriesObject[url] = {code, key, soft}
        })

        this.model.set('categoriesAdd', this.categoriesObject)
    },

    getAllRepacks: function() {
        let repacks = $('.requirements-container .repack:not(.deleted)')

        let that = this
        repacks.each(function() {
            let key  = $(this).find('.repack-label').val()
            let url  = $(this).find('.repack-url').val()
            let code = $(this).data('code')

            if (key && url)
                that.repacksObject[url] = {code, key}
        })

        this.model.set('repacksAdd', this.repacksObject)
    },

    save: function () {
        if (this.isFormSubmitting)
            return
        this.isFormSubmitting = true

        this.getAllCategories()
        this.getAllRepacks()

        this.model.save(null, {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (model, response) => {
                if (response.refresh)
                    location.reload()

                this.loader.removeClass('show')
                this.isFormSubmitting = false
            },
            error: (model, error) => {
                if (error?.responseJSON?.message)
                    new AlertView().errorWindowShow($('.error'), error.responseJSON.message)

                this.loader.removeClass('show')
                this.isFormSubmitting = false
            }
        })
    }
})

let DynamicDomain = new DynamicModel()
new DynamicMenu({model: DynamicDomain})
