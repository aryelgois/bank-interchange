var main = {
    aside_menu: function () {
        var href = $(this).attr('href');
        if (href.startsWith('#')) {
            $('body > main > section').hide().filter(href).show();
        }

        $(this).addClass('selected').siblings().removeClass('selected');
    },
};

$(document).ready(function () {
    $('body > aside > nav').on('click', 'a', main.aside_menu)
      .children('a[href="' + window.location.hash + '"]').click();
});
