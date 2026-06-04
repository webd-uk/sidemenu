<?php
/*
 * Plugin Name: SideMenu
 * Version: 1.9.0
 * Plugin URI: https://webd.uk/product/sidemenu-upgrade/
 * Description: Injects a sliding side menu / sidebar into any theme!
 * Author: Webd Ltd
 * Author URI: https://webd.uk
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: sidemenu
 */



if (!defined('ABSPATH')) {
    exit('This isn\'t the page you\'re looking for. Move along, move along.');
}



if (!class_exists('sidemenu_class')) {

	class sidemenu_class {

        public static $version = '1.9.0';

        private $mobile_toggles = array(
            'twentyseventeen' => 'button.menu-toggle',
            'astra' => 'button.menu-toggle',
            'betheme' => 'a.responsive-menu-toggle, a.mobile-menu-toggle',
            'twentytwentyone' => 'button#primary-mobile-menu.button',
            'okab' => 'a.dima-btn-nav',
            'Divi' => 'div.mobile_nav.closed',
            'varia-wpcom' => '#toggle-menu',
            'varia' => '#toggle-menu',
            'AT10' => 'a.menu-toggle',
            'twentytwentytwo' => 'button.wp-block-navigation__responsive-container-open',
            'twentytwenty' => 'button.toggle.nav-toggle.mobile-nav-toggle',
            'inspiro' => 'button.navbar-toggle',
            'genesis' => 'button.menu-toggle',
            'genesis-block-theme' => 'button.menu-toggle.button-toggle',
            'Avada' => '.fusion-mobile-selector',
            'avanam' => '.drawer-toggle'
        );

        private $remove_mobile_toggle = array(
            'varia-wpcom' => '#toggle',
            'varia' => '#toggle',
            'Avada' => '[id^=mobile-menu-], [class^=fusion-mobile-menu-'
        );

        private $remove_toggle_attribute = array(
            'twentytwentytwo' => 'data-micromodal-trigger'
        );

        private $reconstruct_toggle = array(
            'twentytwenty' => true,
            'Divi' => true,
            'avanam' => true
        );

		function __construct() {

            add_action('widgets_init', array($this, 'sidemenu_sidebar_init'));
            add_action('customize_register', array($this, 'sidemenu_customize_register'));
            add_action('after_setup_theme', array($this, 'sidemenu_register_nav_menu'));
            add_filter('nav_menu_meta_box_object', array($this, 'sidemenu_add_menu_meta_box'));
            add_filter('hidden_meta_boxes', array($this, 'sidemenu_hidden_meta_boxes'), 10, 3);

            if (is_admin()) {

                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'sidemenu_add_plugin_action_links'));
                add_action('admin_notices', 'sidemenuCommon::admin_notices');
                add_action('wp_ajax_dismiss_sidemenu_notice_handler', 'sidemenuCommon::ajax_notice_handler');
                add_filter('widget_form_callback', array($this, 'sidemenu_widget_form_callback'), 10, 2);
                add_action('admin_head', array($this, 'sidemenu_admin_head'));
                add_action('customize_controls_print_styles', array($this, 'sidemenu_admin_head'));
                add_action('customize_controls_enqueue_scripts', array($this, 'sidemenu_enqueue_customize_controls_js'));

            } else {

                add_action('customize_preview_init', array($this, 'sidemenu_enqueue_customize_preview_js'));
                add_action('wp_enqueue_scripts', array($this, 'sidemenu_enqueue_scripts'));
                add_action('wp_footer', array($this, 'sidemenu_inject_sidebar'));
                add_action('wp_head' , array($this, 'sidemenu_header_output'), 11);
                add_shortcode('sidemenu', array($this, 'sidemenu_shortcode'));
                add_filter('dynamic_sidebar_params', array($this, 'sidemenu_dynamic_sidebar_params'));

            }

            add_action('init', 'sidemenu_class::sidemenu_init');

		}

		function sidemenu_add_plugin_action_links($links) {

			$settings_links = sidemenuCommon::plugin_action_links(add_query_arg('autofocus[panel]', 'sidemenu', admin_url('customize.php')));

			return array_merge($settings_links, $links);

		}

        function sidemenu_customize_register($wp_customize) {

            $section_description = sidemenuCommon::control_section_description();
            $upgrade_nag = sidemenuCommon::control_setting_upgrade_nag();



            $wp_customize->add_section('sidemenu', array(
                'title'     => __('SideMenu', 'sidemenu'),
                'description'  => __('Use these options to customize the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_panel('sidemenu', array(
                'priority'       => 0,
                'title'          => __('SideMenu', 'sidemenu'),
                'description'    => __('Use these options to customize the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description
            ));

            $wp_customize->add_section('sidemenu_general', array(
                'panel' => 'sidemenu',
                'title'     => __('General Options', 'sidemenu'),
                'description'  => __('Use these options to customize general settings of the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_section('sidemenu_open_button', array(
                'panel' => 'sidemenu',
                'title'     => __('Open Button', 'sidemenu'),
                'description'  => __('Use these options to customize the open button of the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_section('sidemenu_close_button', array(
                'panel' => 'sidemenu',
                'title'     => __('Close Button', 'sidemenu'),
                'description'  => __('Use these options to customize the close button of the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_section('sidemenu_menu_items', array(
                'panel' => 'sidemenu',
                'title'     => __('Menu Items', 'sidemenu'),
                'description'  => __('Use these options to customize the menu items in the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_section('sidemenu_submenus', array(
                'panel' => 'sidemenu',
                'title'     => __('Submenus', 'sidemenu'),
                'description'  => __('Use these options to customize submenus in the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_section('sidemenu_colors', array(
                'panel' => 'sidemenu',
                'title'     => __('Colors', 'sidemenu'),
                'description'  => __('Use these options to customize color settings for the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_section('sidemenu_style', array(
                'panel' => 'sidemenu',
                'title'     => __('Style', 'sidemenu'),
                'description'  => __('Use these options to customize style settings for the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_section('sidemenu_auto_open', array(
                'panel' => 'sidemenu',
                'title'     => __('Auto Open', 'sidemenu'),
                'description'  => __('Use these options to customize auto open settings for the SideMenu in the current theme.', 'sidemenu') . ' ' . $section_description,
                'priority'     => 0
            ));



            $wp_customize->add_setting('sidemenu_hide_tooltips', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sidemenuCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('sidemenu_hide_tooltips', array(
                'label'         => __('Hide Tooltips', 'sidemenu'),
                'description'   => __('Prevent SideMenu from auto-opening when an admin is signed in and no SideMenu open button or content is detected.', 'sidemenu'),
                'section'       => 'sidemenu_general',
                'settings'      => 'sidemenu_hide_tooltips',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('sidemenu_position', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sidemenuCommon::sanitize_options'
            ));
            $wp_customize->add_control('sidemenu_position', array(
                'label'         => __('SideMenu Position', 'sidemenu'),
                'description'   => __('Decide if the SideMenu should appear on the left or the right.', 'sidemenu'),
                'section'       => 'sidemenu_general',
                'settings'      => 'sidemenu_position',
                'type'          => 'select',
                'choices'       => array(
                    'left' => __('Left', 'sidemenu'),
                    '' => __('Right', 'sidemenu')
                )
            ));

            $wp_customize->add_setting('sidemenu_use_dashicon', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sidemenuCommon::sanitize_options'
            ));
            $wp_customize->add_control('sidemenu_use_dashicon', array(
                'label'         => __('Use Hamburger Dashicon', 'sidemenu'),
                'description'   => __('Use a hamburger Dashicon on the button used to open the SideMenu.', 'sidemenu'),
                'section'       => 'sidemenu_open_button',
                'settings'      => 'sidemenu_use_dashicon',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('No Dashicon (Default)', 'sidemenu'),
                    'before' => __('Before Title', 'sidemenu'),
                    'replace' => __('Replace Title', 'sidemenu'),
                    'after' => __('After Title', 'sidemenu')
                )
            ));

            $control_label = __('Hijack Mobile Menu Button', 'sidemenu');

            if (isset($this->mobile_toggles[get_template()])) {

                $wp_customize->add_setting('sidemenu_hijack_toggle', array(
                    'default'       => '',
                    'type'          => 'theme_mod',
                    'transport'     => 'refresh',
                    'sanitize_callback' => 'sidemenuCommon::sanitize_boolean'
                ));
                $wp_customize->add_control('sidemenu_hijack_toggle', array(
                    'label'         => __('Hijack Mobile Menu Button', 'sidemenu'),
                    'description'   => __('Use the theme\'s existing mobile menu button to open the SideMenu.', 'sidemenu'),
                    'section'       => 'sidemenu_open_button',
                    'settings'      => 'sidemenu_hijack_toggle',
                    'type'          => 'checkbox'
                ));

            } else {

/* translators: link to plugin support forum */
                $control_description = sprintf(wp_kses(__('If you\'d like to be able to open the SideMenu with your theme\'s existing mobile menu button, <a href="%s">submit a feature request</a>.', 'sidemenu'), array('a' => array('href' => array(), 'class' => array()))), esc_url('https://wordpress.org/support/plugin/sidemenu/'));
                sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_hijack_toggle', 'sidemenu_open_button', $control_label, $control_description, 10);

            }

            $wp_customize->add_setting('sidemenu_menu_item_border', array(
                'default'           => 0,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('sidemenu_menu_item_border', array(
                'label'         => __('Menu Item Border', 'sidemenu'),
                'description'   => __('Add a border to the left of each menu item in the SideMenu.', 'sidemenu'),
                'section'       => 'sidemenu_menu_items',
                'settings'      => 'sidemenu_menu_item_border',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 10,
                    'step'  => 1
                ),
            ));

            $wp_customize->add_setting('sidemenu_enable_dropdown', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sidemenuCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('sidemenu_enable_dropdown', array(
                'label'         => __('Enable Dropdown Menus', 'sidemenu'),
                'description'   => __('Enable "Mega Menu" style dropdown menus in the SideMenu Menu Location and Navigation Menu widgets.', 'sidemenu'),
                'section'       => 'sidemenu_submenus',
                'settings'      => 'sidemenu_enable_dropdown',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('sidemenu_submenu_child_count', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sidemenuCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('sidemenu_submenu_child_count', array(
                'label'         => __('Show Submenu Count', 'sidemenu'),
                'description'   => __('Show the number of menu items in brackets in a submenu.', 'sidemenu'),
                'section'       => 'sidemenu_submenus',
                'settings'      => 'sidemenu_submenu_child_count',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('sidemenu_hide_scrollbar', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sidemenuCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('sidemenu_hide_scrollbar', array(
                'label'         => __('Hide SideMenu Scrollbar', 'sidemenu'),
                'description'   => __('Hide vertical scrollbar in the SideMenu on Windows browsers when content extends the height.', 'sidemenu'),
                'section'       => 'sidemenu_general',
                'settings'      => 'sidemenu_hide_scrollbar',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('sidemenu_z_index', array(
                'default'           => 9999,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('sidemenu_z_index', array(
                'label'         => __('SideMenu z-index', 'sidemenu'),
                'description'   => __('Most people can leave this as it is but if you find that something on the site is hiding the SideMenu, you may need to increase the z-index property of the SideMenu.', 'sidemenu'),
                'section'       => 'sidemenu_general',
                'settings'      => 'sidemenu_z_index',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 9999,
                    'max'   => 99999,
                    'step'  => 1
                ),
            ));

            $wp_customize->add_setting('sidemenu_menu_item_border_color', array(
                'default'       => '#ffffff',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sidemenu_menu_item_border_color', array(
                'label'         => __('Menu Item Border Color', 'sidemenu'),
                'description'   => __('Set the color of the border on menu items if one has been added.', 'sidemenu'),
                'section'       => 'sidemenu_colors',
            	'settings'      => 'sidemenu_menu_item_border_color'
            )));

            $wp_customize->add_setting('sidemenu_menu_item_border_hover_color', array(
                'default'       => '#000000',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sidemenu_menu_item_border_hover_color', array(
                'label'         => __('Menu Item Border Hover Color', 'sidemenu'),
                'description'   => __('Set the hover color of the border on menu items if one has been added.', 'sidemenu'),
                'section'       => 'sidemenu_colors',
            	'settings'      => 'sidemenu_menu_item_border_hover_color'
            )));

            $control_label = __('Dashicon', 'sidemenu');
/* translators: link dashicon documentation */
            $control_description = sprintf(wp_kses(__('Choose your own <a href="%s">dashicon</a> for the button that opens the SideMenu.', 'sidemenu'), array('a' => array('href' => array()))), esc_url('https://developer.wordpress.org/resource/dashicons/'));
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_dashicon', 'sidemenu_open_button', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Auto Open', 'sidemenu');
            $control_description = __('Set which pages the SideMenu should open on automatically.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_auto_open', 'sidemenu_auto_open', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Auto-Open Delay', 'sidemenu');
            $control_description = __('Set the delay before the SideMenu opens on posts and pages set to auto-open the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_auto_open_delay', 'sidemenu_auto_open', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Dashicon Size', 'sidemenu');
            $control_description = __('Set the size of the dashicon.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_dashicon_size', 'sidemenu_open_button', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Transition Duration', 'sidemenu');
            $control_description = __('Set how long it takes for the SideMenu to open.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_transition_duration', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Transition Effect', 'sidemenu');
            $control_description = __('Choose the animaation effect when opening the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_transition_effect', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Overlay Color', 'sidemenu');
            $control_description = __('Set the background color of the overlay that dims the main website.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_overlay_color', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Overlay Opacity', 'sidemenu');
            $control_description = __('Set the opacity of the overlay that dims the main website.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_overlay_opacity', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Desktop SideMenu Width', 'sidemenu');
            $control_description = __('Set the width of the SideMenu as a percentage of the display width on larger screens.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_width', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Desktop SideMenu Minimum Width', 'sidemenu');
            $control_description = __('Set the minimum width of the SideMenu on larger screens.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_min_width', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Mobile SideMenu Width', 'sidemenu');
            $control_description = __('Set the width of the SideMenu as a percentage of the display width on smaller screens.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_width_mobile', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Mobile SideMenu Minimum Width', 'sidemenu');
            $control_description = __('Set the minimum width of the SideMenu on smaller screens.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_min_width_mobile', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Rounded Corners', 'sidemenu');
            $control_description = __('Add rounded corners to the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_rounded_corners', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Height', 'sidemenu');
            $control_description = __('Set the height of the SideMenu as a percentage of the display height.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_height', 'sidemenu_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grow SideMenu Content', 'sidemenu');
            $control_description = __('Spreads out the content of the SideMenu so that it fills the available height.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_grow_content', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('No Content Animation', 'sidemenu');
            $control_description = __('Prevents the site content from moving when the SideMenu opens.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_no_content_animation', 'sidemenu_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Prevent Scroll Close', 'sidemenu');
            $control_description = __('Prevents user scrolling from closing the sidemenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_prevent_scroll_close', 'sidemenu_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Prevent Content Click Close', 'sidemenu');
            $control_description = __('Prevents user clicking on the site content from closing the sidemenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_prevent_content_click_close', 'sidemenu_general', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Background Color', 'sidemenu');
            $control_description = __('Set the background color of the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_background_color', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Hide Close Button', 'sidemenu');
            $control_description = __('Do not show the SideMenu close button.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_hide_close', 'sidemenu_close_button', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Title Alignment', 'sidemenu');
            $control_description = __('Align the titles in the SideMenu to the left, center or right.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_title_text_align', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Close Dashicon', 'sidemenu');
/* translators: link to dashicon documentation */
            $control_description = sprintf(wp_kses(__('Choose your own <a href="%s">dashicon</a> for the button that closes the SideMenu.', 'sidemenu'), array('a' => array('href' => array()))), esc_url('https://developer.wordpress.org/resource/dashicons/'));
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_close_dashicon', 'sidemenu_close_button', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Rounded Corner', 'sidemenu');
            $control_description = __('Add a rounded corner to the SideMenu close icon (make sure you give it a background color).', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_close_rounded_corner', 'sidemenu_close_button', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Close Icon Color', 'sidemenu');
            $control_description = __('Set the color of the close icon in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_close_icon_color', 'sidemenu_colors', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Close Icon Background Color', 'sidemenu');
            $control_description = __('Set the color of the close icon background in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_close_icon_background_color', 'sidemenu_colors', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Title Font Case', 'sidemenu');
            $control_description = __('Change the font case of the titles in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_title_text_transform', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Title Font Size', 'sidemenu');
            $control_description = __('Change the font size of the titles in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_title_font_size', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Title Font Weight', 'sidemenu');
            $control_description = __('Change the font weight of the titles in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_title_font_weight', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Title Color', 'sidemenu');
            $control_description = __('Set the color of titles in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_title_color', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Menu Padding', 'sidemenu');
            $control_description = __('Set the padding inbetween menu items in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_menu_padding', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Sub-Menu Toggle Dashicon', 'sidemenu');
/* translators: link to the dashicon documentation */
            $control_description = sprintf(wp_kses(__('Choose your own <a href="%s">dashicon</a> for the sub-menu toggle button.', 'sidemenu'), array('a' => array('href' => array()))), esc_url('https://developer.wordpress.org/resource/dashicons/'));
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_submenu_toggle', 'sidemenu_submenus', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Sub-Menu Toggle Rotation', 'sidemenu');
            $control_description = __('Choose how the sub-menu toggle should rotate on click.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_sub_menu_toggle_rotate', 'sidemenu_submenus', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Smaller Sub-Menu Items', 'sidemenu');
            $control_description = __('Shows sub-menu items with a smaller font size than their parent\'s.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_smaller_submenu', 'sidemenu_submenus', $label = $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Text Alignment', 'sidemenu');
            $control_description = __('Align the text in the SideMenu to the left, center or right.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_text_align', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Text Font Case', 'sidemenu');
            $control_description = __('Change the font case of the text in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_text_transform', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Text Font Size', 'sidemenu');
            $control_description = __('Change the font size of the text in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_text_font_size', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Text Color', 'sidemenu');
            $control_description = __('Set the color of text in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_text_color', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Link Color', 'sidemenu');
            $control_description = __('Set the color of links in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_link_color', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('SideMenu Link Hover Color', 'sidemenu');
            $control_description = __('Set the color of hovered links in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_link_hover_color', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Section Border Width', 'sidemenu');
            $control_description = __('Set the width of the border inbetween sections in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_section_border_width', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Section Border Color', 'sidemenu');
            $control_description = __('Set the color of the border inbetween sections in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_section_border_color', 'sidemenu_colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Section Border Style', 'sidemenu');
            $control_description = __('Set the style of the border inbetween sections in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_section_border_style', 'sidemenu_style', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Menu Item Hover Animation', 'sidemenu');
            $control_description = __('Adds a hover animation underneath each menu item in the SideMenu.', 'sidemenu');
            sidemenuCommon::add_hidden_control($wp_customize, 'sidemenu_menu_item_hover_anim', 'sidemenu_menu_items', $label = $control_label, $control_description . ' ' . $upgrade_nag);

        }

        function sidemenu_enqueue_scripts() {

            if (!is_admin()) {

                wp_enqueue_style('sidemenu-css', plugin_dir_url(__FILE__) . 'css/sidemenu.css', array(), sidemenuCommon::plugin_version());
        		wp_enqueue_script('sidemenu-js', plugin_dir_url(__FILE__) . 'js/sidemenu.js', array(), sidemenuCommon::plugin_version(), true);

                $l10n = array(
                    'arialabel' => __('Open menu', 'sidemenu')
                );

                if (get_theme_mod('sidemenu_hide_tooltips')) {

                    $l10n['hidetooltips'] = true;

                }

                if (current_user_can('manage_options')) { 

/* translators: link to legacy menu dashboard */
                    $l10n['openbuttonhelp'] = '<h2>' . __('Nearly there ...', 'sidemenu') . '</h2><p>' . sprintf(wp_kses(__('The SideMenu has opened because you are signed in as a administrator. You need to add a button to open the SideMenu on this page by adding a <a href="%s">menu item</a> or by using a [sidemenu] shortcode.', 'sidemenu'), array('a' => array('href' => array()))), esc_url(admin_url('nav-menus.php'))) . '</p>';

                }

                $mod = get_theme_mod('sidemenu_use_dashicon');

                if ($mod) {

                    if (class_exists('sidemenuPremium') && sidemenuPremium::request_permission(true) && get_theme_mod('sidemenu_dashicon')) {

                        $l10n['dashicon'] = get_theme_mod('sidemenu_dashicon');

                    } else {

                        $l10n['dashicon'] = 'menu-alt';

                    }

                    $l10n['dashiconlocation'] = $mod;

                }

                if (get_theme_mod('sidemenu_hijack_toggle') && isset($this->mobile_toggles[get_template()])) {

                    $l10n['hijacktoggle'] = $this->mobile_toggles[get_template()];

                } else {

                    $l10n['hijacktoggle'] = false;

                }

                if (get_theme_mod('sidemenu_hijack_toggle') && isset($this->remove_mobile_toggle[get_template()])) {

                    $l10n['removetoggle'] = $this->remove_mobile_toggle[get_template()];

                } else {

                    $l10n['removetoggle'] = false;

                }

                if (get_theme_mod('sidemenu_hijack_toggle') && isset($this->remove_toggle_attribute[get_template()])) {

                    $l10n['removeattribute'] = $this->remove_toggle_attribute[get_template()];

                } else {

                    $l10n['removeattribute'] = false;

                }

                if (get_theme_mod('sidemenu_hijack_toggle') && isset($this->reconstruct_toggle[get_template()])) {

                    $l10n['reconstructtoggle'] = $this->reconstruct_toggle[get_template()];

                } else {

                    $l10n['reconstructtoggle'] = false;

                }

                if (get_theme_mod('sidemenu_prevent_scroll_close') && class_exists('sidemenuPremium') && sidemenuPremium::request_permission(true)) {

                    $l10n['scrollclose'] = false;

                } else {

                    $l10n['scrollclose'] = true;

                }

                if (get_theme_mod('sidemenu_prevent_content_click_close') && class_exists('sidemenuPremium') && sidemenuPremium::request_permission(true)) {

                    $l10n['contentclickclose'] = false;

                } else {

                    $l10n['contentclickclose'] = true;

                }

                if (get_theme_mod('sidemenu_enable_dropdown')) {

                    $l10n['dropdown'] = true;

                }

                if (get_theme_mod('sidemenu_submenu_child_count')) {

                    $l10n['dropdowncount'] = true;

                }

                wp_localize_script(
                    'sidemenu-js',
                    'sideMenu',
                    $l10n
                );
                wp_enqueue_style('dashicons');

            }

        }

        function sidemenu_inject_sidebar() {

            if (!(defined('REST_REQUEST') && REST_REQUEST)) {

                $show_close_icon = true;
                $close_dashicon = 'no-alt';

                if (class_exists('sidemenuPremium') && sidemenuPremium::request_permission(true)) {

                    if (get_theme_mod('sidemenu_close_dashicon')) {

                        $close_dashicon = sanitize_key(get_theme_mod('sidemenu_close_dashicon'));

                    }

                    if (get_theme_mod('sidemenu_hide_close')) {

                        $show_close_icon = false;

                    }

                }

?><section class="sidemenu"><?php

                if ($show_close_icon) {

?><a href="#" class="close_sidemenu" title="<?php esc_attr_e('Close menu', 'sidemenu'); ?>" aria-label="<?php esc_attr_e('Close menu', 'sidemenu'); ?>"><span class="dashicons dashicons-<?php echo esc_attr($close_dashicon); ?>"></span></a><?php

                }

    		    if (is_active_sidebar('sidemenu')) {

                    dynamic_sidebar('sidemenu');

    		    }

    			if (has_nav_menu('sidemenu')) {

?><section class="sidemenu-navigation" role="navigation" aria-label="<?php esc_attr_e( 'SideMenu', 'sidemenu' ); ?>"><?php

                    wp_nav_menu(
                        array(
    					    'theme_location' => 'sidemenu'
    				    )
    				);

?></section><!-- .sidemenu-navigation --><?php

    			}

    		    if (!is_active_sidebar('sidemenu') && !has_nav_menu('sidemenu') && current_user_can('manage_options')) {

/* translators: links to legacy widgets and menu dashboards */
?><section><h2><?php esc_html_e('And finally ...', 'sidemenu'); ?></h2><p><?php printf(wp_kses(__('Add some content to your SideMenu by adding a <a href="%1$s">widget</a> and / or a <a href="%2$s">menu</a> to the SideMenu.', 'sidemenu'), array('a' => array('href' => array()))), esc_url(admin_url('widgets.php')), esc_url(admin_url('nav-menus.php'))); ?></p></section><?php

    		    }

?></section><?php

                if (get_theme_mod('sidemenu_hijack_toggle')) {

?><span id="sidemenu_hijack_toggle" class="open_sidemenu"><a href="#">SideMenu</a></span><?php

                }

            }

        }

        function sidemenu_sidebar_init() {

            register_sidebar(array(
        		'name'          => __('SideMenu', 'sidemenu'),
        		'id'            => 'sidemenu',
        		'description'   => __('Add widgets here to appear in the SideMenu.', 'sidemenu'),
		        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        		'after_widget'  => '</section>',
        		'before_title'  => '<h2 class="widget-title">',
        		'after_title'   => '</h2>',
            ));

        }

        function sidemenu_register_nav_menu() {

        	register_nav_menus(
        		array(
        			'sidemenu' => __('SideMenu', 'sidemenu')
        		)
        	);

        }

        function sidemenu_add_menu_meta_box($post_type) {

            add_meta_box(
                'sidemenu-menu-metabox',
                __('Open SideMenu', 'sidemenu'),
                array($this, 'sidemenu_menu_metabox'),
                'nav-menus',
                'side',
                'high'
            );

            return $post_type;

        }

        function sidemenu_menu_metabox() {

        	global $nav_menu_selected_id;

        	$walker = new Walker_Nav_Menu_Checklist();
        	$current_tab = 'all';
            $sidemenu_items = array();

            $sidemenu_items[] = (object) array(
                'classes' => array('open_sidemenu'),
                'type' => 'custom',
                'object_id' => 1,
                'title' => __('SideMenu', 'sidemenu'),
                'object' => 'custom',
                'url' => '#',
                'attr_title' => __('Open menu', 'sidemenu'),
                'db_id' => 0,
                'menu_item_parent' => 0,
                'target' => '',
                'xfn' => ''
            );

        	$removed_args = array( 'action', 'customlink-tab', 'edit-menu-item', 'menu-item', 'page-tab', '_wpnonce' );

?>

	<div id="sectionarchive" class="categorydiv">

		<ul id="sectionarchive-tabs" class="sectionarchive-tabs add-menu-item-tabs">
			<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-sectionarchive-all" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg( 'sectionarchive-tab', 'all', remove_query_arg( $removed_args ) ) ); ?>#tabs-panel-sectionarchive-all">
					<?php esc_html_e('View All', 'sidemenu'); ?>
				</a>
			</li><!-- /.tabs -->
		</ul>

		<div id="tabs-panel-sectionarchive-all" class="tabs-panel tabs-panel-view-all <?php echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>">
			<ul id="sectionarchive-checklist-all" class="categorychecklist form-no-clear">
			<?php
				echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $sidemenu_items), 0, (object) array( 'walker' => $walker));
			?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls wp-clearfix">
			<span class="list-controls">
				<a href="<?php echo esc_url( add_query_arg( array( 'sectionarchive-tab' => 'all', 'selectall' => 1, ), remove_query_arg( $removed_args ) )); ?>#sectionarchive" class="select-all"><?php esc_html_e('Select All', 'sidemenu'); ?></a>
			</span>
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu', 'sidemenu'); ?>" name="add-sectionarchive-menu-item" id="submit-sectionarchive" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.categorydiv -->

<?php

        }

        public function sidemenu_hidden_meta_boxes($hidden, $screen, $use_defaults) {

            if (is_object($screen) && isset($screen->base) && 'nav-menus' === $screen->base) {

                $hidden = array_diff($hidden, array('sidemenu-menu-metabox'));

            }

            return $hidden;

        }

        function sidemenu_header_output() {

?>
<!--Customizer CSS--> 
<style type="text/css">
<?php

            if ('twentytwentyone' === get_template()) {

?>
.sidemenu .sub-menu-toggle {
    display: none;
}
<?php

            }

            if (get_theme_mod('sidemenu_position')) {

?>
.sidemenu {
    right: auto;
    left: -100%;
}

@media screen and (min-width: 48em) {
    .sidemenu {
        left: -45%;
    }
}

.sidemenu_open .sidemenu {
    right: auto;
    left: 0;
}

.sidemenu_open>div {
    transform: translateX(15%);
}
<?php

            }

?>
.sidemenu section:last-child {
    border-bottom: none;
}
<?php

            $mod = absint(get_theme_mod('sidemenu_menu_item_border'));

            if ($mod) {

                $mod_color = get_theme_mod('sidemenu_menu_item_border_color');
                if (!$mod_color) { $mod_color = 'white'; }

                $mod_hover_color = get_theme_mod('sidemenu_menu_item_border_hover_color');
                if (!$mod_hover_color) { $mod_hover_color = 'black'; }

?>
.widget_nav_menu a, .sidemenu-navigation a {
    border-left: <?php echo wp_kses($mod, 'strip'); ?>px solid <?php echo wp_kses($mod_color, 'strip'); ?>;
    padding-left: 10px;
}
.widget_nav_menu a:hover, .sidemenu-navigation a:hover {
    border-left-color: <?php echo wp_kses($mod_hover_color, 'strip'); ?>;
}
<?php

            }

            if (get_theme_mod('sidemenu_enable_dropdown')) {

                add_filter('walker_nav_menu_start_el', array($this, 'sidemenu_add_sub_menu_toggle'), 10, 4);

?>
.submenu-toggle {
    display: none;
}
.sidemenu .submenu-toggle {
    display: inline-block;
	transition: transform 0.25s ease-in-out;
	cursor: pointer;
    padding: 0;
    width: auto;
    height: auto;
    margin-left: 1rem;
    line-height: inherit;
}
.sidemenu .submenu-toggle.open {
    transform: rotate(90deg);
}
.sidemenu section ul {
	padding-left: 0;
}
.sidemenu .sub-menu {
	display: none;
}
.sidemenu .sub-menu.open {
	display: block;
}
<?php

            }

            if (get_theme_mod('sidemenu_hide_scrollbar')) {

?>
.sidemenu {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.sidemenu::-webkit-scrollbar {
     display: none;
}
<?php

            }

            sidemenuCommon::generate_css('.sidemenu', 'z-index', 'sidemenu_z_index');

?>
</style> 
<!--/Customizer CSS-->
<?php

        }

        public function sidemenu_shortcode($atts = array(), $content = 'SideMenu') {

            if (!is_array($atts)) { $atts = array(); }

            if (!$content) { $content = 'SideMenu'; }

            if (isset($atts['title'])) { $content = $atts['title']; }

            if (isset($atts['class'])) {

                $atts['class'] = ' ' . sanitize_html_class($atts['class']);

            } else {

                $atts['class'] = '';

            }

            return '<span class="open_sidemenu' . $atts['class'] . '"><a href="#">' . sanitize_text_field($content) . '</a></span>';

        }

        function sidemenu_admin_head() {

            global $pagenow;

            if ($pagenow === 'widgets.php' || $pagenow === 'customize.php') {

?>
<style>
    .sidemenu-class { display: none; }
    #sidemenu .sidemenu-class, #sub-accordion-section-sidebar-widgets-sidemenu .sidemenu-class { display: block; }
</style>
<?php

            }

        }

        function sidemenu_widget_form_callback( $instance, $widget ) {

            if (isset($widget->id)) { echo wp_kses('<p class="sidemenu-class">SideMenu Class: <strong>' . $widget->id . '</strong></p>', 'post'); }

        	return $instance;

        }

        function sidemenu_dynamic_sidebar_params( $params ) {

        	$params[0]['before_widget'] = preg_replace('/class="/', "class=\"{$params[0]['widget_id']} ", $params[0]['before_widget'], 1);

        	return $params;

        }

        function sidemenu_enqueue_customize_preview_js() {

            wp_enqueue_script('sidemenu-customize-preview', plugin_dir_url(__FILE__) . 'js/customize-preview.js', array('jquery', 'customize-preview'), sidemenuCommon::plugin_version(), true);

        }

        function sidemenu_enqueue_customize_controls_js() {

            wp_enqueue_script('sidemenu-customize-controls', plugin_dir_url(__FILE__) . 'js/customize-controls.js', array('jquery', 'customize-controls'), sidemenuCommon::plugin_version(), true);


        }

        public function sidemenu_add_sub_menu_toggle($output, $item, $depth, $args) {

            if (((isset($args->theme_location) && $args->theme_location === 'sidemenu') || (isset($args->theme_location) && !$args->theme_location)) && in_array('menu-item-has-children', $item->classes, true)) {

                $dashicon = sanitize_key(get_theme_mod('sidemenu_submenu_toggle'));

                if (!$dashicon) {

                    $dashicon = 'arrow-right-alt2';

                }

                $output .= '<span class="submenu-toggle dashicons dashicons-dashicons dashicons-' . $dashicon. '"></span>';

            }

            return $output;

        }

        public static function sidemenu_init() {

        	register_block_type(__DIR__ . '/build');

        }

	}

    if (!class_exists('sidemenuCommon')) {

        require_once(dirname(__FILE__) . '/includes/class-sidemenu-common.php');

    }

    $sidemenu_object = new sidemenu_class();

}

?>
