var main = {
    aside_menu: function () {
        var href = $(this).attr('href');
        if (href.startsWith('#')) {
            $('body > main > section').hide().filter(href).show();
        }

        $(this).addClass('selected').siblings().removeClass('selected');
    },

    ajax_get: function (selector, script, request, callback) {
        $.get('/example/ajax/' + script, request, function (data) {
            var el = $(selector);

            el.children(':not(.persistent)').remove();

            el.append(data);

            if (typeof callback == 'function') {
                callback.call(el);
            }
        });
    },
};

$(document).ready(function () {
    $('body > aside > nav').on('click', 'a', main.aside_menu)
      .children('a[href="' + window.location.hash + '"]').click();

    $('#address_country').change(function () {
        main.ajax_get(
            '#address_state',
            'get_states.php',
            {country: $(this).val()}
        );
    });
    $('#address_state').change(function () {
        main.ajax_get(
            '#address_county',
            'get_counties.php',
            {state: $(this).val()}
        );
    });

    main.ajax_get('#address_country', 'get_countries.php', null, function () {
        this.change();
    });

    main.ajax_get('#assignor_fields [name=bank]', 'get_banks.php');
    main.ajax_get('#assignor_fields [name=wallet]', 'get_wallets.php');

    main.ajax_get('#payer_list', 'get_people.php', {'class': 'Payer'});
    main.ajax_get('#assignor_list', 'get_people.php', {'class': 'Assignor'});

    main.ajax_get('#generate_shipping_file tbody', 'get_titles.php');
    main.ajax_get('#generate_cnab tbody', 'get_shipping_files.php');
});
