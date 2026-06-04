(function($) {

    wp.customize.bind('ready', function() {

        wp.customize('sidemenu_z_index', function(this_customizer_control) { initiate_show_value(this_customizer_control,
            '' // postfix
        ); });

        wp.customize('sidemenu_menu_item_border', function(this_customizer_control) { initiate_show_value(this_customizer_control,
            '' // postfix
        ); });

        function initiate_show_value(customizer_control, value_postfix = 'px', value_multiplier = 1, value_deductor = 0) {
            show_value(wp.customize(customizer_control.id).get(), customizer_control.id, value_postfix, value_multiplier, value_deductor);
            show_value_bind(customizer_control, value_postfix, value_multiplier, value_deductor);
        }

        function show_value_bind(customizer_control, value_postfix, value_multiplier, value_deductor) {
            customizer_control.bind(function(value) {
                show_value(value, customizer_control.id, value_postfix, value_multiplier, value_deductor);
            });
        }

        function show_value(value, control_name, value_postfix, value_multiplier, value_deductor) {
            if ($('#_customize-description-' + control_name + '-value').length) {
                $('#_customize-description-' + control_name + '-value').text(parseFloat(((value - value_deductor) * value_multiplier).toFixed(3)) + value_postfix);
            } else {
                $('#_customize-description-' + control_name + '').append($('<br /><strong id="_customize-description-' + control_name + '-value">' + parseFloat(((value - value_deductor) * value_multiplier).toFixed(3)) + value_postfix + '</strong>'));
            }
        }

    });

})(jQuery);
