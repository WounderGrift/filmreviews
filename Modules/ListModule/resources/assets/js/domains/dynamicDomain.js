let DynamicDomain = Backbone.Model.extend({
    defaults: {
        categoriesAdd:    [],
        repacksAdd:       [],
        categoriesRemove: [],
        repacksRemove:    []
    },

    url: `/dynamic-menu/save`
})

export { DynamicDomain };
