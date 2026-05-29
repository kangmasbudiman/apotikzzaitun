const viewport = jQuery(window)
const responsive = 575.99

if (viewport.width() <= responsive) {
    jQuery('body').removeClass('open')
}

viewport.resize(function () {
    const width = viewport.width()

    if (width <= responsive) {
        jQuery('body').removeClass('open')
    } else {
        jQuery('body').addClass('open')
    }
})

jQuery('#laporan').on('show.bs.dropdown', function () {
    for (let el of jQuery(this).find('.today')) {
        let value = jQuery(el).html()

        jQuery(el).html(value += ' Harian')
    }
})

jQuery('#laporan').on('hide.bs.dropdown', function () {
    for (let el of jQuery(this).find('.today')) {
        let value = jQuery(el).html()

        jQuery(el).html(value.substr(0, value.length - 7))
    }
})

jQuery('#bulanan').on('show.bs.dropdown', function () {
    for (let el of jQuery(this).find('.today')) {
        let value = jQuery(el).html()

        jQuery(el).html(value += ' Bulanan')
    }
})

jQuery('#bulanan').on('hide.bs.dropdown', function () {
    for (let el of jQuery(this).find('.today')) {
        let value = jQuery(el).html()

        jQuery(el).html(value.substr(0, value.length - 8))
    }
})