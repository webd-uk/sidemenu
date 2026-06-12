<?php
/*
 * Plugin Name: SideMenu
 * Version: 2.0
 * Plugin URI: https://webd.uk/product/sidemenu-upgrade/
 * Description: Adds a customizable sliding side menu to your WordPress site.
 * Author: Webd Ltd
 * Author URI: https://webd.uk
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 6.2
 * Text Domain: sidemenu
 */

if (!defined('ABSPATH')) {
    exit('This isn\'t the page you\'re looking for. Move along, move along.');
}

if (!class_exists('sidemenu_block_class')) {

    class sidemenu_block_class {

        const VERSION = '2.0';

        // Plugin storage identifiers.
        const CLASSIC_USAGE_OPTION = 'sidemenu_classic_usage_detected';
        const DEFAULT_CONTENT_OPTION = 'sidemenu_default_content_id';
        const CONTENT_POST_TYPE = 'sidemenu';

        // Per-request block rendering state.
        private $block_content_ids = array();
        private $block_panels = array();
        private $rendering_sidemenu_content = false;

        public function init() {

            if (self::sidemenu_should_load_classic_runtime()) {

                self::sidemenu_load_classic_runtime();

            }

            // Register the block and shared SideMenu content post type.
            add_action('init', array($this, 'sidemenu_register_block_content'));

            // Pass SideMenu content options to the block editor.
            add_action('enqueue_block_editor_assets', array($this, 'sidemenu_enqueue_block_editor_assets'));

            // Register REST endpoints used by the block editor.
            add_action('rest_api_init', array($this, 'sidemenu_rest_api_init'));

            add_filter('allowed_block_types_all', array($this, 'sidemenu_filter_allowed_block_types'), 10, 2);

            if (!self::sidemenu_should_load_classic_runtime() && self::sidemenu_is_classic_settings_request()) {

                // Explain why Classic Customizer settings are unavailable when opened from an unsaved block.
                add_action('customize_register', array($this, 'sidemenu_customize_register'));

            }

            // Detect block-mode buttons and render their SideMenu content.
            add_filter('render_block', array($this, 'sidemenu_render_block'), 10, 2);
            add_action('wp_footer', array($this, 'sidemenu_output_block_panels'));

            // Refresh classic runtime detection when post content changes.
            add_action('wp_after_insert_post', array($this, 'sidemenu_refresh_classic_usage_option_after_insert_post'), 10, 4);
            add_action('deleted_post', array($this, 'sidemenu_refresh_classic_usage_option_after_post_change'));
            add_action('trashed_post', array($this, 'sidemenu_refresh_classic_usage_option_after_post_change'));
            add_action('untrashed_post', array($this, 'sidemenu_refresh_classic_usage_option_after_post_change'));
            add_action('switch_theme', array($this, 'sidemenu_refresh_classic_usage_option_after_post_change'));
            add_action('wp_update_nav_menu', array($this, 'sidemenu_refresh_classic_usage_option'));

            // Refresh classic runtime detection when block widget content changes.
            add_action('updated_option', array($this, 'sidemenu_refresh_classic_usage_option_after_option_change'), 10, 1);
            add_action('added_option', array($this, 'sidemenu_refresh_classic_usage_option_after_option_change'), 10, 1);
            add_action('deleted_option', array($this, 'sidemenu_refresh_classic_usage_option_after_option_change'), 10, 1);

            // Build the cached classic runtime detection option when the plugin is activated.
            register_activation_hook(__FILE__, array('sidemenu_block_class', 'sidemenu_refresh_classic_usage_option'));

            if (is_admin()) {

                // Add settings, upgrade and trial links on the Plugins screen.
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'sidemenu_add_plugin_action_links'));

                // Show and handle plugin-level admin notices.
                add_action('admin_notices', array($this, 'sidemenu_admin_notices'));
                add_action('wp_ajax_dismiss_sidemenu_notice_handler', 'sidemenuCommon::ajax_notice_handler');
                add_action('wp_ajax_dismiss_sidemenu_block_notice_handler', array($this, 'sidemenu_block_ajax_notice_handler'));

            }

        }

        public static function plugin_path() {

            return plugin_dir_path(__FILE__);

        }

        public static function plugin_url() {

            return plugin_dir_url(__FILE__);

        }

        public function sidemenu_add_plugin_action_links($links) {

            $settings_link = false;

            if (self::sidemenu_should_load_classic_runtime()) {

                $settings_link = add_query_arg('autofocus[panel]', 'sidemenu', admin_url('customize.php'));
                $settings_links = sidemenuCommon::plugin_action_links($settings_link, false, 'classic');
                $settings_links[] = '<a href="' . esc_url(sidemenuCommon::upgrade_link('block')) . '" title="' . esc_attr__('Upgrade SideMenu block features', 'sidemenu') . '" style="color: orange; font-weight: bold;">' . esc_html__('Block Upgrade', 'sidemenu') . '</a>';

            } else {

                $settings_links = sidemenuCommon::plugin_action_links($settings_link, false, 'block');

            }

            return array_merge($settings_links, $links);

        }

        public function sidemenu_admin_notices() {

            if (self::sidemenu_should_load_classic_runtime()) {

                sidemenuCommon::admin_notices();

                return;

            }

            sidemenuCommon::admin_notices('block');

        }

        public function sidemenu_block_ajax_notice_handler() {

            sidemenuCommon::ajax_notice_handler('block');

        }

        public function sidemenu_register_block_content() {

            register_block_type(self::plugin_path() . 'build');
            register_post_meta(
                self::CONTENT_POST_TYPE,
                '_sidemenu_position',
                array(
                    'single' => true,
                    'type' => 'string',
                    'default' => 'right',
                    'sanitize_callback' => array('sidemenu_block_class', 'sidemenu_sanitize_position'),
                    'auth_callback' => array('sidemenu_block_class', 'sidemenu_can_edit_meta'),
                    'show_in_rest' => true,
                )
            );

            if (post_type_exists(self::CONTENT_POST_TYPE)) {

                return;

            }

            $labels = array(
                'name' => __('SideMenus', 'sidemenu'),
                'singular_name' => __('SideMenu', 'sidemenu'),
                'menu_name' => __('SideMenus', 'sidemenu'),
                'all_items' => __('SideMenus', 'sidemenu'),
                'add_new' => __('Add New', 'sidemenu'),
                'add_new_item' => __('Add new SideMenu', 'sidemenu'),
                'edit_item' => __('Edit SideMenu', 'sidemenu'),
                'new_item' => __('New SideMenu', 'sidemenu'),
                'view_item' => __('View SideMenu', 'sidemenu'),
                'view_items' => __('View SideMenus', 'sidemenu'),
                'search_items' => __('Search SideMenus', 'sidemenu'),
                'not_found' => __('No SideMenus found', 'sidemenu'),
                'not_found_in_trash' => __('No SideMenus found in bin', 'sidemenu'),
                'name_admin_bar' => __('SideMenu', 'sidemenu'),
            );

            register_post_type(
                self::CONTENT_POST_TYPE,
                array(
                    'label' => __('SideMenus', 'sidemenu'),
                    'labels' => $labels,
                    'description' => __('Reusable SideMenu content for the SideMenu block.', 'sidemenu'),
                    'public' => true,
                    'publicly_queryable' => false,
                    'show_ui' => true,
                    'show_in_rest' => true,
                    'has_archive' => false,
                    'show_in_menu' => true,
                    'show_in_nav_menus' => false,
                    'delete_with_user' => false,
                    'exclude_from_search' => true,
                    'capability_type' => 'post',
                    'map_meta_cap' => true,
                    'hierarchical' => false,
                    'rewrite' => false,
                    'query_var' => false,
                    'menu_position' => 20,
                    'menu_icon' => 'dashicons-menu',
                    'supports' => array('title', 'editor', 'custom-fields'),
                    'template' => self::sidemenu_content_template(),
                )
            );

        }

        public static function sidemenu_content_template() {

            $example_blocks = array(
                array(
                    'core/heading',
                    array(
                        'content' => __('Example SideMenu', 'sidemenu'),
                    ),
                ),
                array(
                    'core/paragraph',
                    array(
                        'content' => __('Add and arrange blocks here to create your SideMenu content.', 'sidemenu'),
                    ),
                ),
            );
            $block_registry = WP_Block_Type_Registry::get_instance();

            if ($block_registry->is_registered('core/accordion')) {

                $example_blocks[] = array(
                    'core/accordion',
                    array(
                        'iconPosition' => 'left',
                        'autoclose' => true,
                    ),
                    array(
                        array(
                            'core/accordion-item',
                            array(),
                            array(
                                array(
                                    'core/accordion-heading',
                                    array(
                                        'title' => __('Useful links', 'sidemenu'),
                                        'iconPosition' => 'left',
                                    ),
                                ),
                                array(
                                    'core/accordion-panel',
                                    array(),
                                    array(
                                        array(
                                            'core/list',
                                            array(),
                                            array(
                                                array(
                                                    'core/list-item',
                                                    array(
                                                        'content' => __('Menu item 1', 'sidemenu'),
                                                    ),
                                                ),
                                                array(
                                                    'core/list-item',
                                                    array(
                                                        'content' => __('Menu item 2', 'sidemenu'),
                                                    ),
                                                ),
                                                array(
                                                    'core/list-item',
                                                    array(
                                                        'content' => __('Menu item 3', 'sidemenu'),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'core/accordion-item',
                            array(),
                            array(
                                array(
                                    'core/accordion-heading',
                                    array(
                                        'title' => __('More information', 'sidemenu'),
                                        'iconPosition' => 'left',
                                    ),
                                ),
                                array(
                                    'core/accordion-panel',
                                    array(),
                                    array(
                                        array(
                                            'core/paragraph',
                                            array(
                                                'content' => __('Add more SideMenu content here.', 'sidemenu'),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                );

            } else {

                $example_blocks[] = array(
                    'core/navigation',
                    array(
                        'overlayMenu' => 'never',
                        'layout' => array(
                            'type' => 'flex',
                            'orientation' => 'vertical',
                        ),
                    ),
                    array(
                        array('core/page-list'),
                    ),
                );

            }

            return array(
                array(
                    'core/group',
                    array(
                        'align' => 'full',
                        'style' => array(
                            'color' => array(
                                'background' => '#666666',
                                'text' => '#ffffff',
                            ),
                            'elements' => array(
                                'link' => array(
                                    'color' => array(
                                        'text' => '#d3d3d3',
                                    ),
                                ),
                            ),
                            'dimensions' => array(
                                'minHeight' => 'var(--sidemenu-block-panel-height, 100vh)',
                            ),
                            'spacing' => array(
                                'padding' => array(
                                    'top' => '4rem',
                                    'right' => '2rem',
                                    'bottom' => '2rem',
                                    'left' => '2rem',
                                ),
                            ),
                        ),
                        'layout' => array(
                            'type' => 'default',
                        ),
                    ),
                    $example_blocks
                ),
            );

        }

        public static function sidemenu_allowed_positions() {

            return array('right', 'left');

        }

        public static function sidemenu_sanitize_position($position) {

            $position = sanitize_key($position);

            if (!in_array($position, self::sidemenu_allowed_positions(), true)) {

                return 'right';

            }

            return $position;

        }

        public static function sidemenu_can_edit_meta($allowed = true, $meta_key = '', $post_id = 0) {

            if ('_sidemenu_position' !== $meta_key) {

                return false;

            }

            if ($post_id) {

                if (self::CONTENT_POST_TYPE !== get_post_type($post_id)) {

                    return false;

                }

                return current_user_can('edit_post', $post_id);

            }

            return false;

        }

        public static function sidemenu_get_panel_position($content_id) {

            return self::sidemenu_sanitize_position(get_post_meta(absint($content_id), '_sidemenu_position', true));

        }

        public static function sidemenu_get_content_posts() {

            $posts = get_posts(
                array(
                    'numberposts' => -1,
                    'post_type' => self::CONTENT_POST_TYPE,
                    'post_status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC',
                )
            );

            $content_posts = array();

            foreach ($posts as $post) {

                $content_posts[] = array(
                    'id' => absint($post->ID),
                    /* translators: %d: SideMenu content post ID. */
                    'title' => $post->post_title ? $post->post_title : sprintf(__('SideMenu #%d', 'sidemenu'), absint($post->ID)),
                    'editLink' => current_user_can('edit_post', $post->ID) ? get_edit_post_link($post->ID, '') : '',
                );

            }

            return $content_posts;

        }

        public static function sidemenu_get_default_content_id($maybe_set = true) {

            $default_content_id = absint(get_option(self::DEFAULT_CONTENT_OPTION));

            if (
                $default_content_id &&
                'publish' === get_post_status($default_content_id) &&
                self::CONTENT_POST_TYPE === get_post_type($default_content_id)
            ) {

                return $default_content_id;

            }

            if ($default_content_id) {

                delete_option(self::DEFAULT_CONTENT_OPTION);
                $default_content_id = 0;

            }

            $content_posts = self::sidemenu_get_content_posts();

            if ($maybe_set && count($content_posts)) {

                $default_content_id = absint($content_posts[0]['id']);
                update_option(self::DEFAULT_CONTENT_OPTION, $default_content_id, false);

            }

            return $default_content_id;

        }

        public static function sidemenu_can_create_content_posts() {

            if (!self::sidemenu_can_use_block()) {

                return false;

            }

            $post_type = get_post_type_object(self::CONTENT_POST_TYPE);

            if ($post_type && isset($post_type->cap->create_posts)) {

                return current_user_can($post_type->cap->create_posts);

            }

            return current_user_can('edit_pages');

        }

        public static function sidemenu_can_use_block() {

            return (bool) apply_filters('sidemenu_can_use_block', current_user_can('edit_pages'));

        }

        public function sidemenu_filter_allowed_block_types($allowed_block_types, $block_editor_context) {

            if (self::sidemenu_can_use_block()) {

                return $allowed_block_types;

            }

            if (true === $allowed_block_types) {

                $registered_block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
                $allowed_block_types = array_keys($registered_block_types);

            }

            if (!is_array($allowed_block_types)) {

                return $allowed_block_types;

            }

            return array_values(array_diff($allowed_block_types, array('sidemenu/sidemenu')));

        }

        public function sidemenu_enqueue_block_editor_assets() {

            $can_create_content = self::sidemenu_can_create_content_posts();
            $premium_trial_date = absint(get_option('sidemenu_block_trial_date'));
            $premium_purchased = (bool) get_option('sidemenu_block_purchased');
            $premium_trial_active = $premium_trial_date && time() < strtotime('+1 week', $premium_trial_date);
            $data = array(
                'contentPosts' => self::sidemenu_get_content_posts(),
                'defaultContentId' => self::sidemenu_get_default_content_id(),
                'contentPostType' => self::CONTENT_POST_TYPE,
                'createContentUrl' => $can_create_content ? admin_url('post-new.php?post_type=' . self::CONTENT_POST_TYPE) : '',
                'canCreateContent' => $can_create_content,
                'customizeUrl' => self::sidemenu_classic_customizer_url(),
                'classicCustomizerAvailable' => self::sidemenu_should_load_classic_runtime(),
                'restNamespace' => 'sidemenu/v1',
                'premiumBlockActive' => false,
                'premiumTrialAvailable' => !$premium_purchased && (!$premium_trial_date || $premium_trial_active),
                'premiumTrialStarted' => (bool) $premium_trial_date,
                'premiumPurchased' => $premium_purchased,
                'premiumDownloadUrl' => sidemenuCommon::premium_link('block'),
                'premiumUpgradeUrl' => sidemenuCommon::upgrade_link('block'),
            );

            wp_add_inline_script(
                'sidemenu-sidemenu-editor-script',
                'window.sideMenuBlock = ' . wp_json_encode($data) . ';',
                'before'
            );

        }

        public function sidemenu_customize_register($wp_customize) {

            if (self::sidemenu_should_load_classic_runtime()) {

                return;

            }

            $wp_customize->add_section('sidemenu', array(
                'priority'       => 0,
                'title'          => __('SideMenu', 'sidemenu'),
                'description'  => __('No classic SideMenu Open Button has been detected and the theme is a block theme so settings are unavailable.', 'sidemenu')
            ));

            $wp_customize->add_control('sidemenu_classic_missing_notice', array(
                'section'  => 'sidemenu',
                'type'     => 'hidden',
                'settings' => array()
            ));

        }

        public static function sidemenu_classic_customizer_url() {

            $autofocus = self::sidemenu_should_load_classic_runtime() ? 'autofocus[panel]' : 'autofocus[section]';

            return add_query_arg(
                array(
                    $autofocus => 'sidemenu',
                    'sidemenu_classic_settings' => 1,
                ),
                admin_url('customize.php')
            );

        }

        public function sidemenu_rest_api_init() {

            register_rest_route(
                'sidemenu/v1',
                '/content',
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'sidemenu_rest_get_content'),
                    'permission_callback' => array($this, 'sidemenu_rest_can_edit'),
                )
            );

            register_rest_route(
                'sidemenu/v1',
                '/default-content',
                array(
                    'methods' => 'POST',
                    'callback' => array($this, 'sidemenu_rest_set_default_content'),
                    'permission_callback' => array($this, 'sidemenu_rest_can_edit'),
                    'args' => array(
                        'contentId' => array(
                            'type' => 'integer',
                            'required' => true,
                            'sanitize_callback' => 'absint',
                        ),
                    ),
                )
            );

            register_rest_route(
                'sidemenu/v1',
                '/classic-usage',
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'sidemenu_rest_get_classic_usage'),
                    'permission_callback' => array($this, 'sidemenu_rest_can_edit'),
                )
            );

        }

        public function sidemenu_rest_can_edit() {

            return self::sidemenu_can_use_block();

        }

        public function sidemenu_rest_get_content() {

            return rest_ensure_response(
                array(
                    'contentPosts' => self::sidemenu_get_content_posts(),
                    'defaultContentId' => self::sidemenu_get_default_content_id(),
                )
            );

        }

        public function sidemenu_rest_get_classic_usage() {

            return rest_ensure_response(
                array(
                    'classicCustomizerAvailable' => self::sidemenu_should_load_classic_runtime(),
                    'customizeUrl' => self::sidemenu_classic_customizer_url(),
                )
            );

        }

        public function sidemenu_rest_set_default_content($request) {

            $content_id = absint($request->get_param('contentId'));

            if (
                $content_id &&
                (
                    'publish' !== get_post_status($content_id) ||
                    self::CONTENT_POST_TYPE !== get_post_type($content_id)
                )
            ) {

                return new WP_Error('sidemenu_invalid_content_id', __('Invalid SideMenu content.', 'sidemenu'), array('status' => 400));

            }

            if ($content_id) {

                update_option(self::DEFAULT_CONTENT_OPTION, $content_id, false);

            } else {

                delete_option(self::DEFAULT_CONTENT_OPTION);

            }

            return $this->sidemenu_rest_get_content();

        }

        public static function sidemenu_get_block_content_id($attributes = array()) {

            $content_id = self::sidemenu_get_default_content_id();

            return absint(apply_filters('sidemenu_block_content_id', $content_id, $attributes));

        }

        public static function sidemenu_enqueue_block_frontend_assets() {

            wp_enqueue_style('sidemenu-block-css', self::plugin_url() . 'css/sidemenu-block.css', array(), self::VERSION);
            wp_enqueue_script('sidemenu-block-js', self::plugin_url() . 'js/sidemenu-block.js', array(), self::VERSION, true);
            $frontend_data = apply_filters(
                'sidemenu_block_frontend_data',
                array(
                    'openLabel' => __('Open menu', 'sidemenu'),
                    'closeColor' => self::sidemenu_get_default_close_color(),
                )
            );
            wp_localize_script(
                'sidemenu-block-js',
                'sideMenuBlockFrontend',
                $frontend_data
            );

        }

        public static function sidemenu_get_default_close_color() {

            $close_color = '#000000';

            if (function_exists('wp_theme_has_theme_json') && wp_theme_has_theme_json() && function_exists('wp_get_global_styles')) {

                $global_styles_color = wp_get_global_styles(array('color'));

                if (!empty($global_styles_color['text'])) {

                    $close_color = sanitize_hex_color($global_styles_color['text']);

                    if (!$close_color) {

                        $close_color = sanitize_text_field($global_styles_color['text']);

                    }

                }

            }

            return $close_color;

        }

        public static function sidemenu_get_panel_settings($content_id) {

            $settings = array(
                'transitionDuration' => '0.2s',
                'desktopWidth' => '45%',
                'desktopMinWidth' => '',
                'mobileWidth' => '100%',
                'mobileMinWidth' => '',
                'height' => 'var(--sidemenu-block-panel-height, 100vh)',
                'roundedCorners' => '',
                'scrollClose' => true,
                'coverClickClose' => true,
                'autoOpen' => false,
                'autoOpenDelay' => 0,
                'hideCloseButton' => false,
                'closeIconAnimation' => true,
                'closeIconSize' => '32px',
                'coverColor' => 'rgba(0, 0, 0, 0.6)',
            );

            return apply_filters('sidemenu_block_panel_settings', $settings, absint($content_id));

        }

        public static function sidemenu_sanitize_css_value($value) {

            return preg_replace('/[^a-zA-Z0-9\s\.\,\-\_\%\(\)\/\:]/', '', (string) $value);

        }

        public static function sidemenu_panel_style($settings) {

            $style_map = array(
                'transitionDuration' => '--sidemenu-block-transition-duration',
                'desktopWidth' => '--sidemenu-block-desktop-width',
                'desktopMinWidth' => '--sidemenu-block-desktop-min-width',
                'mobileWidth' => '--sidemenu-block-mobile-width',
                'mobileMinWidth' => '--sidemenu-block-mobile-min-width',
                'height' => '--sidemenu-block-panel-custom-height',
                'roundedCorners' => '--sidemenu-block-border-radius',
                'closeIconSize' => '--sidemenu-block-close-icon-size',
            );
            $styles = array();

            foreach ($style_map as $setting_key => $css_variable) {

                if (!empty($settings[$setting_key])) {

                    $styles[] = $css_variable . ': ' . self::sidemenu_sanitize_css_value($settings[$setting_key]);

                }

            }

            return implode('; ', $styles);

        }

        public static function sidemenu_bool_attr($value) {

            return $value ? 'true' : 'false';

        }

        public static function sidemenu_close_icon_svg($content_id, $settings) {

            $svg = '<svg class="sidemenu-block-close-icon" xmlns="http://www.w3.org/2000/svg" viewBox="3 3 18 18" aria-hidden="true" focusable="false"><path d="m13.06 12 4.22-4.22-1.06-1.06L12 10.94 7.78 6.72 6.72 7.78 10.94 12l-4.22 4.22 1.06 1.06L12 13.06l4.22 4.22 1.06-1.06L13.06 12Z" /></svg>';

            return apply_filters('sidemenu_block_close_icon_svg', $svg, absint($content_id), $settings);

        }

        public static function sidemenu_block_close_button_html($content_id, $settings) {

            if (!empty($settings['hideCloseButton'])) {

                return '';

            }

            $close_class = 'sidemenu-block-close';

            if (isset($settings['closeIconAnimation']) && !$settings['closeIconAnimation']) {

                $close_class .= ' sidemenu-block-close-no-animation';

            }

            $close_html = '<button type="button" class="' . esc_attr($close_class) . '" aria-label="' . esc_attr__('Close menu', 'sidemenu') . '">' . self::sidemenu_close_icon_svg($content_id, $settings) . '</button>';

            return apply_filters('sidemenu_block_close_button_html', $close_html, absint($content_id), $settings);

        }

        public static function sidemenu_allowed_close_button_html() {

            return array(
                'button' => array(
                    'type' => true,
                    'class' => true,
                    'aria-label' => true,
                ),
                'svg' => array(
                    'class' => true,
                    'xmlns' => true,
                    'viewbox' => true,
                    'aria-hidden' => true,
                    'focusable' => true,
                ),
                'path' => array(
                    'd' => true,
                    'fill' => true,
                    'fill-rule' => true,
                    'transform' => true,
                ),
                'polygon' => array(
                    'fill' => true,
                    'fill-rule' => true,
                    'points' => true,
                    'transform' => true,
                ),
                'circle' => array(
                    'cx' => true,
                    'cy' => true,
                    'r' => true,
                    'fill' => true,
                    'transform' => true,
                ),
                'rect' => array(
                    'x' => true,
                    'y' => true,
                    'width' => true,
                    'height' => true,
                    'fill' => true,
                    'transform' => true,
                ),
            );

        }

        public static function sidemenu_add_block_data_attribute($block_content, $content_id) {

            if (class_exists('WP_HTML_Tag_Processor')) {

                $processor = new WP_HTML_Tag_Processor($block_content);

                if ($processor->next_tag()) {

                    $processor->set_attribute('data-sidemenu-content-id', absint($content_id));

                    return $processor->get_updated_html();

                }

            }

            return preg_replace(
                '/<div([^>]*)class="([^"\n]*wp-block-sidemenu-sidemenu[^"\n]*)"([^>]*)>/',
                '<div$1class="$2"$3 data-sidemenu-content-id="' . absint($content_id) . '">',
                $block_content,
                1
            );

        }

        public function sidemenu_render_block($block_content, $block) {

            if (
                $this->rendering_sidemenu_content ||
                !$block_content ||
                !is_array($block) ||
                !isset($block['blockName']) ||
                'sidemenu/sidemenu' !== $block['blockName']
            ) {

                return $block_content;

            }

            $attributes = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();

            if (
                !isset($attributes['mode']) ||
                'classic' === $attributes['mode']
            ) {

                return $block_content;

            }

            $content_id = self::sidemenu_get_block_content_id($attributes);

            if (!$content_id) {

                return $block_content;

            }

            self::sidemenu_enqueue_block_frontend_assets();
            $block_content = self::sidemenu_add_block_data_attribute($block_content, $content_id);
            $block_content = apply_filters('sidemenu_block_open_button_html', $block_content, $attributes, $content_id);
            $this->sidemenu_render_block_panel($content_id);

            return $block_content;

        }

        public function sidemenu_render_content_post($content_id) {

            $content_post = get_post(absint($content_id));

            if (
                !$content_post ||
                self::CONTENT_POST_TYPE !== $content_post->post_type ||
                'publish' !== $content_post->post_status
            ) {

                return '';

            }

            $this->rendering_sidemenu_content = true;
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Applies the core content filter to SideMenu content posts.
            $content = apply_filters('the_content', $content_post->post_content);
            $this->rendering_sidemenu_content = false;

            return $content;

        }

        public function sidemenu_render_block_panel($content_id) {

            $content_id = absint($content_id);

            if (
                !$content_id ||
                in_array($content_id, $this->block_content_ids, true)
            ) {

                return '';

            }

            $content = $this->sidemenu_render_content_post($content_id);

            if (!$content) {

                return '';

            }

            $this->block_content_ids[] = $content_id;
            $position = self::sidemenu_get_panel_position($content_id);
            $settings = self::sidemenu_get_panel_settings($content_id);
            $panel_style = self::sidemenu_panel_style($settings);
            $close_button = self::sidemenu_block_close_button_html($content_id, $settings);

            ob_start();

?><section class="sidemenu-block-panel sidemenu-position-<?php echo esc_attr($position); ?>" id="sidemenu-block-panel-<?php echo esc_attr($content_id); ?>" data-sidemenu-panel-id="<?php echo esc_attr($content_id); ?>" data-sidemenu-position="<?php echo esc_attr($position); ?>" data-sidemenu-scroll-close="<?php echo esc_attr(self::sidemenu_bool_attr(!empty($settings['scrollClose']))); ?>" data-sidemenu-cover-click-close="<?php echo esc_attr(self::sidemenu_bool_attr(!empty($settings['coverClickClose']))); ?>" data-sidemenu-auto-open="<?php echo esc_attr(self::sidemenu_bool_attr(!empty($settings['autoOpen']))); ?>" data-sidemenu-auto-open-delay="<?php echo esc_attr(absint($settings['autoOpenDelay'])); ?>" data-sidemenu-cover-color="<?php echo esc_attr($settings['coverColor']); ?>"<?php echo $panel_style ? ' style="' . esc_attr($panel_style) . '"' : ''; ?> aria-hidden="true">
    <?php echo wp_kses($close_button, self::sidemenu_allowed_close_button_html()); ?>
    <div class="sidemenu-block-content"><?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content has passed through WordPress's standard the_content pipeline.
        echo $content;
    ?></div>
</section><?php

            $this->block_panels[$content_id] = ob_get_clean();

            return '';

        }

        public function sidemenu_output_block_panels() {

            if (!$this->block_panels) {

                return;

            }

?><div class="sidemenu-block-panels">
<?php
            foreach ($this->block_panels as $panel) {

                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Panel markup is generated and escaped by sidemenu_render_block_panel().
                echo $panel;

            }
?></div>
<div class="sidemenu-block-cover" hidden></div><?php

        }

        public static function sidemenu_is_block_theme() {

            if (function_exists('wp_get_theme')) {

                $theme = wp_get_theme();

                if (is_object($theme) && method_exists($theme, 'is_block_theme')) {

                    return $theme->is_block_theme();

                }

            }

            if (function_exists('wp_is_block_theme')) {

                return wp_is_block_theme();

            }

            return false;

        }

        public static function sidemenu_block_has_classic_trigger($block) {

            if (!is_array($block)) {

                return false;

            }

            $block_name = isset($block['blockName']) ? $block['blockName'] : null;
            $attrs = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();

            if ('sidemenu/sidemenu' === $block_name) {

                return !isset($attrs['mode']) || 'classic' === $attrs['mode'];

            }

            if (
                !(
                    'core/button' === $block_name &&
                    !empty($attrs['sidemenuOpenButton'])
                )
            ) {

                $inner_html = isset($block['innerHTML']) && is_string($block['innerHTML']) ? $block['innerHTML'] : '';

                if (
                    self::sidemenu_content_has_classic_open_button($inner_html) ||
                    self::sidemenu_content_has_classic_shortcode($inner_html)
                ) {

                    return true;

                }

            }

            if (isset($block['innerBlocks']) && is_array($block['innerBlocks'])) {

                foreach ($block['innerBlocks'] as $inner_block) {

                    if (self::sidemenu_block_has_classic_trigger($inner_block)) {

                        return true;

                    }

                }

            }

            return false;

        }

        public static function sidemenu_content_has_classic_block($content) {

            if (
                !is_string($content) ||
                (
                    false === strpos($content, 'wp:sidemenu/sidemenu') &&
                    false === strpos($content, 'open_sidemenu') &&
                    false === strpos($content, '[sidemenu')
                )
            ) {

                return false;

            }

            if (!function_exists('parse_blocks')) {

                return (
                    self::sidemenu_content_has_classic_open_button($content) ||
                    self::sidemenu_content_has_classic_shortcode($content) ||
                    false !== strpos($content, '<!-- wp:sidemenu/sidemenu -->') ||
                    false !== strpos($content, '"mode":"classic"')
                );

            }

            $blocks = parse_blocks($content);

            foreach ($blocks as $block) {

                if (self::sidemenu_block_has_classic_trigger($block)) {

                    return true;

                }

            }

            return false;

        }

        public static function sidemenu_content_may_have_classic_triggers($content) {

            return is_string($content) && (
                false !== strpos($content, 'wp:sidemenu/sidemenu') ||
                false !== strpos($content, 'open_sidemenu') ||
                false !== strpos($content, '[sidemenu')
            );

        }

        public static function sidemenu_content_has_classic_open_button($content) {

            if (!is_string($content) || false === strpos($content, 'open_sidemenu')) {

                return false;

            }

            if (class_exists('WP_HTML_Tag_Processor')) {

                $processor = new WP_HTML_Tag_Processor($content);

                while ($processor->next_tag()) {

                    $class = $processor->get_attribute('class');

                    if (!$class) {

                        continue;

                    }

                    $classes = preg_split('/\s+/', trim($class));

                    if (
                        in_array('open_sidemenu', $classes, true) &&
                        !in_array('sidemenu-block-mode', $classes, true)
                    ) {

                        return true;

                    }

                }

                return false;

            }

            return (bool) preg_match('/class=(["\'])(?=[^"\']*\bopen_sidemenu\b)(?![^"\']*\bsidemenu-block-mode\b)[^"\']*\1/', $content);

        }

        public static function sidemenu_content_has_classic_shortcode($content) {

            if (!is_string($content) || false === strpos($content, '[sidemenu')) {

                return false;

            }

            return (bool) preg_match('/\[sidemenu(?:[\s\]\/])/', $content);

        }

        public static function sidemenu_value_has_classic_block($value) {

            if (is_string($value)) {

                return self::sidemenu_content_has_classic_block($value);

            }

            if (is_array($value)) {

                foreach ($value as $item) {

                    if (self::sidemenu_value_has_classic_block($item)) {

                        return true;

                    }

                }

            }

            return false;

        }

        public static function sidemenu_published_content_has_classic_triggers() {

            global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $contents = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_content FROM {$wpdb->posts} WHERE post_status = %s AND (post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s)",
                    'publish',
                    '%wp:sidemenu/sidemenu%',
                    '%open_sidemenu%',
                    '%[sidemenu%'
                )
            );

            foreach ($contents as $content) {

                if (self::sidemenu_content_has_classic_block($content)) {

                    return true;

                }

            }

            return false;

        }

        public static function sidemenu_widgets_have_classic_usage() {

            $sidebars_widgets = get_option('sidebars_widgets', array());
            $block_widgets = get_option('widget_block', array());

            if (!is_array($sidebars_widgets) || !is_array($block_widgets)) {

                return false;

            }

            foreach ($sidebars_widgets as $sidebar_id => $widget_ids) {

                if ('wp_inactive_widgets' === $sidebar_id || !is_array($widget_ids)) {

                    continue;

                }

                foreach ($widget_ids as $widget_id) {

                    if (!preg_match('/^block-(\d+)$/', $widget_id, $matches)) {

                        continue;

                    }

                    $widget_number = absint($matches[1]);

                    if (!empty($block_widgets[$widget_number]['content']) && self::sidemenu_content_has_classic_block($block_widgets[$widget_number]['content'])) {

                        return true;

                    }

                }

            }

            return false;

        }

        public static function sidemenu_detect_classic_usage() {

            return (
                self::sidemenu_theme_has_classic_usage() ||
                self::sidemenu_nav_menus_have_classic_triggers() ||
                self::sidemenu_published_content_has_classic_triggers() ||
                self::sidemenu_widgets_have_classic_usage()
            );

        }

        public static function sidemenu_theme_has_classic_usage() {

            return (bool) get_theme_mod('sidemenu_hijack_toggle');

        }

        public static function sidemenu_nav_menus_have_classic_triggers() {

            $menu_locations = get_nav_menu_locations();

            if (!is_array($menu_locations)) {

                return false;

            }

            foreach ($menu_locations as $location => $menu_id) {

                if ('sidemenu' === $location || !$menu_id) {

                    continue;

                }

                $menu_items = wp_get_nav_menu_items($menu_id);

                if (!is_array($menu_items)) {

                    continue;

                }

                foreach ($menu_items as $menu_item) {

                    $classes = isset($menu_item->classes) && is_array($menu_item->classes) ? $menu_item->classes : array();

                    if (in_array('open_sidemenu', $classes, true)) {

                        return true;

                    }

                }

            }

            return false;

        }

        public static function sidemenu_refresh_classic_usage_option() {

            static $refreshing = false;

            if ($refreshing) {

                return;

            }

            $refreshing = true;

            $has_classic_usage = (bool) self::sidemenu_detect_classic_usage();

            if (null === get_option(self::CLASSIC_USAGE_OPTION, null)) {

                add_option(self::CLASSIC_USAGE_OPTION, $has_classic_usage, '', false);

            } else {

                update_option(self::CLASSIC_USAGE_OPTION, $has_classic_usage, false);

            }

            $refreshing = false;

        }

        public static function sidemenu_has_classic_usage() {

            $has_classic_usage = get_option(self::CLASSIC_USAGE_OPTION, null);

            if (null === $has_classic_usage) {

                self::sidemenu_refresh_classic_usage_option();
                $has_classic_usage = get_option(self::CLASSIC_USAGE_OPTION, false);

            }

            return (bool) $has_classic_usage;

        }

        public static function sidemenu_should_load_classic_runtime() {

            if (!self::sidemenu_is_block_theme()) {

                return true;

            }

            return self::sidemenu_has_classic_usage();

        }

        public static function sidemenu_is_classic_settings_request() {

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing flag for the Customizer settings screen.
            return isset($_GET['sidemenu_classic_settings']) && '1' === sanitize_text_field(wp_unslash($_GET['sidemenu_classic_settings']));

        }

        public static function sidemenu_load_classic_runtime() {

            require_once self::plugin_path() . 'classic-sidemenu.php';

        }

        public function sidemenu_refresh_classic_usage_option_after_post_change($post_id = 0, $post = null) {

            if ($post_id && !self::sidemenu_post_can_affect_classic_usage($post_id, $post)) {

                return;

            }

            self::sidemenu_refresh_classic_usage_option();

        }

        public function sidemenu_refresh_classic_usage_option_after_insert_post($post_id, $post, $update, $post_before) {

            if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {

                return;

            }

            if (!self::sidemenu_post_can_affect_classic_usage($post_id, $post, $post_before)) {

                return;

            }

            self::sidemenu_refresh_classic_usage_option();

        }

        public static function sidemenu_post_can_affect_classic_usage($post_id, $post = null, $post_before = null) {

            $post_id = absint($post_id);

            if (!$post_id) {

                return true;

            }

            if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {

                return false;

            }

            if (!$post instanceof WP_Post) {

                $post = get_post($post_id);

            }

            if (!$post) {

                return true;

            }

            if (
                'publish' === $post->post_status &&
                self::sidemenu_content_may_have_classic_triggers($post->post_content)
            ) {

                return true;

            }

            if (
                $post_before instanceof WP_Post &&
                'publish' === $post_before->post_status &&
                self::sidemenu_content_may_have_classic_triggers($post_before->post_content)
            ) {

                return true;

            }

            return false;

        }

        public function sidemenu_refresh_classic_usage_option_after_option_change($option) {

            if (
                self::CLASSIC_USAGE_OPTION === $option ||
                self::DEFAULT_CONTENT_OPTION === $option
            ) {

                return;

            }

            if (
                in_array($option, array('sidebars_widgets', 'widget_block'), true) ||
                0 === strpos($option, 'theme_mods_')
            ) {

                self::sidemenu_refresh_classic_usage_option();

            }

        }

    }

    if (!class_exists('sidemenuCommon')) {

        require_once(dirname(__FILE__) . '/includes/class-sidemenu-common.php');

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared helper property name is kept for upgrade-helper compatibility.
        sidemenuCommon::$plugin_upgrade_products = array(
            'classic' => array(
                'name' => 'SideMenu',
                'prefix' => 'sidemenu',
                'text_domain' => 'sidemenu',
                'premium_class' => 'sidemenu_premium_class',
                'trial' => true,
                'upgrade' => true,
                'product_slug' => 'sidemenu-upgrade',
                'premium_plugin' => 'sidemenu-premium/sidemenu-premium.php',
            ),
            'block' => array(
                'name' => 'SideMenu',
                'prefix' => 'sidemenu_block',
                'text_domain' => 'sidemenu',
                'premium_class' => 'sidemenu_block_premium_class',
                'trial' => true,
                'upgrade' => true,
                'premium_name' => 'SideMenu Block Premium',
                'product_slug' => 'sidemenu-block-upgrade',
                'premium_plugin' => 'sidemenu-block-premium/sidemenu-block-premium.php',
            ),
        );

    }

    $sidemenu_block = new sidemenu_block_class();
    $sidemenu_block->init();

}
