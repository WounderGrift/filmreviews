$(document).ready(function () {
    sourceDropdown()

    $('#hide_broken').on('click', function (e) {
        e.stopPropagation()
    })

    $(document).on('input', '#searchSource', function() {
        let searchValue = $(this).val().toLowerCase()

        $('.options .option').each(function() {
            let optionText = $(this).text().toLowerCase()
            if (optionText.includes(searchValue)) {
                $(this).show()
            } else {
                $(this).hide()
            }
        })
    })
})

function sourceDropdown() {
    const sourceCategories  = $(".source-dropdown")
    const selectedOptionsCategories  = sourceCategories.find(".selected-options")
    const optionsContainerCategories = sourceCategories.find(".options")
    const optionsCategories = optionsContainerCategories.find(".option")

    selectedOptionsCategories.on('click', function () {
        optionsContainerCategories.toggle()
    })

    optionsCategories.each(function () {
        $(this).on('click', function () {
            $(this).toggleClass("selected")
            updateSelectedOptionsCategory()
        })
    })

    function updateSelectedOptionsCategory() {
        const selectedItems = optionsContainerCategories.find(".selected").map(function () {
            return $(this).text()
        }).get()

        if (selectedItems.length === 0)
            selectedOptionsCategories.html('<span class="placeholder">' +  + '</span>')
        else {
            selectedOptionsCategories.html(selectedItems[0])
        }
    }
}
