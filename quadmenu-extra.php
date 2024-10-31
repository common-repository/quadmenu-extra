<?php
/**
 * Plugin Name: QuadMenu - Extra Mega Menu
 * Plugin URI: https://quadmenu.com/extra/
 * Description: Integrates QuadMenu with the Extra theme.
 * Version: 1.1.4
 * Author: QuadMenu
 * Author URI: https://quadmenu.com
 * License: codecanyon
* License: GPLv3
 */
if (!defined('ABSPATH')) {
    die('-1');
}

if (!class_exists('QuadMenu_Extra')) {

    final class QuadMenu_Extra {

        function __construct() {

            add_action('admin_notices', array($this, 'notices'));

            add_action('wp_enqueue_scripts', array($this, 'scripts'), 10);

            add_action('init', array($this, 'hooks'), -30);

            add_action('init', array($this, 'primary_menu_integration'));

            add_filter('quadmenu_locate_template', array($this, 'theme'), 10, 5);

            add_filter('quadmenu_default_themes', array($this, 'themes'), 10);

            add_filter('quadmenu_developer_options', array($this, 'options'), 10);

            add_filter('quadmenu_default_options_theme_extra', array($this, 'extra'), 10);

            add_filter('quadmenu_default_options_location_primary-menu', array($this, 'defaults'), 10);
        }

        function notices() {

            $screen = get_current_screen();

            if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
                return;
            }

            $plugin = 'quadmenu/quadmenu.php';

            if (is_plugin_active($plugin)) {
                return;
            }

            if (is_quadmenu_installed()) {

                if (!current_user_can('activate_plugins')) {
                    return;
                }
                ?>
                <div class="error">
                    <p>
                        <a href="<?php echo wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1', 'activate-plugin_' . $plugin); ?>" class='button button-secondary'><?php _e('Activate QuadMenu', 'quadmenu'); ?></a>
                        <?php esc_html_e('QuadMenu Extra not working because you need to activate the QuadMenu plugin.', 'quadmenu'); ?>   
                    </p>
                </div>
                <?php
            } else {

                if (!current_user_can('install_plugins')) {
                    return;
                }
                ?>
                <div class="error">
                    <p>
                        <a href="<?php echo wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=quadmenu'), 'install-plugin_quadmenu'); ?>" class='button button-secondary'><?php _e('Install QuadMenu', 'quadmenu'); ?></a>
                        <?php esc_html_e('QuadMenu Extra not working because you need to install the QuadMenu plugin.', 'quadmenu'); ?>
                    </p>
                </div>
                <?php
            }
        }

        static function is_extra() {

            if (!function_exists('et_extra_fonts_url'))
                return false;

            if (!function_exists('et_get_option'))
                return false;

            return true;
        }

        function scripts() {

            if (!self::is_extra())
                return;

            $extra_scripts_dependencies = apply_filters('extra_scripts_dependencies', array('jquery', 'imagesloaded'));

            // Load dependencies conditionally
            if (is_page_template('page-template-authors.php') || 'Masonry' === et_get_option('archive_list_style', 'Standard') || is_page_template('page-template-blog-feed.php')) {
                $extra_scripts_dependencies[] = 'salvattore';
            }

            wp_dequeue_script('extra-scripts');

            wp_enqueue_script('extra-scripts', plugin_dir_url(__FILE__) . 'assets/scripts.min.js', $extra_scripts_dependencies, '', true);
        }

        function hooks() {

            if (!self::is_extra())
                return;

            add_action('wp_enqueue_scripts', array($this, 'enqueue'));

            add_filter('quadmenu_compiler_files', array($this, 'files'));
        }

        function files($files) {

            $files[] = plugin_dir_url(__FILE__) . 'assets/quadmenu-extra.less';

            return $files;
        }

        function enqueue() {

            if (is_file(QUADMENU_PATH_CSS . 'quadmenu-extra.css')) {
                wp_enqueue_style('quadmenu-extra', QUADMENU_URL_CSS . 'quadmenu-extra.css', array(), filemtime(QUADMENU_PATH_CSS . 'quadmenu-extra.css'), 'all');
            }
        }

        function primary_menu_integration() {

            if (!self::is_extra())
                return;

            if (!function_exists('is_quadmenu_location'))
                return;

            if (!is_quadmenu_location('primary-menu'))
                return;

            remove_action('et_header_top', 'extra_add_mobile_navigation');

            add_action('et_header_top', array($this, 'primary_menu'));
        }

        function primary_menu() {

            if (is_customize_preview() || ( 'slide' !== et_get_option('header_style', 'left') && 'fullscreen' !== et_get_option('header_style', 'left') )) {
                ?>
                <div id="et-mobile-navigation">
                    <span class="show-menu">
                        <div class="show-menu-button">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <p><?php esc_html_e('Select Page', 'extra'); ?></p>
                    </span>
                    <?php wp_nav_menu(array('theme_location' => 'primary-menu', 'layout' => 'inherit')); ?>
                </div> <!-- /#et-mobile-navigation -->
                <?php
            }
        }

        function theme($template, $template_name, $template_path, $default_path, $args) {


            if (!self::is_extra())
                return $template;

            if (et_get_option('header_style') === 'slide') {
                return plugin_dir_path(__FILE__) . '/collapsed.php';
            }

            return $template;
        }

        function themes($themes) {

            $themes['extra'] = 'Extra Theme';

            return $themes;
        }

        function options($options) {

            if (!self::is_extra())
                return;

            // Custom
            // ---------------------------------------------------------------------

            $options['menu_height'] = et_get_option('primary_nav_height', '124');
            $options['minimized_menu_height'] = et_get_option('fixed_nav_height', '80');

            $options['viewport'] = 0;

            $options['primary-menu_unwrap'] = 0;

            $options['extra_theme_title'] = 'Extra Theme';

            $options['extra_navbar_logo'] = array(
                'url' => null
            );

            $options['extra_layout_breakpoint'] = 980;

            $options['extra_layout_width'] = 0;

            $options['extra_layout_width_selector'] = '';

            $options['extra_layout_sticky'] = 0;

            $options['extra_layout_sticky_offset'] = 0;

            $options['extra_layout_hover_effect'] = null;

            $options['extra_mobile_shadow'] = 'hide';

            $options['extra_navbar_background'] = 'color';

            $options['extra_navbar_background_color'] = 'transparent';
            $options['extra_navbar_background_to'] = 'transparent';

            $options['extra_navbar'] = '';
            $options['extra_navbar_height'] = '80';
            $options['extra_navbar_width'] = '260';


            return $options;
        }

        function extra($defaults) {

            $defaults['layout'] = 'collapse';
            $defaults['layout_offcanvas_float'] = 'right';
            $defaults['layout_align'] = 'right';
            $defaults['layout_breakpoint'] = '';
            $defaults['layout_width'] = '0';
            $defaults['layout_width_selector'] = '';
            $defaults['layout_trigger'] = 'hoverintent';
            $defaults['layout_current'] = '';
            $defaults['layout_animation'] = 'quadmenu_btt';
            $defaults['layout_classes'] = '';
            $defaults['layout_sticky'] = '0';
            $defaults['layout_sticky_offset'] = '90';
            $defaults['layout_extrader'] = 'hide';
            $defaults['layout_caret'] = 'show';
            $defaults['layout_hover_effect'] = '';
            $defaults['navbar_background'] = 'color';
            $defaults['navbar_background_color'] = 'transparent';
            $defaults['navbar_background_to'] = 'transparent';
            $defaults['navbar_background_deg'] = '17';
            $defaults['navbar_extrader'] = 'transparent';
            $defaults['navbar_text'] = '#8585bd';
            $defaults['navbar_height'] = '90';
            $defaults['navbar_width'] = '260';
            $defaults['navbar_mobile_border'] = 'transparent';
            $defaults['navbar_toggle_open'] = '#2ea3f2';
            $defaults['navbar_toggle_close'] = '#2ea3f2';
            $defaults['navbar_logo'] = array(
                'url' => '',
                'id' => '',
                'height' => '',
                'width' => '',
                'thumbnail' => '',
                'title' => '',
                'caption' => '',
                'alt' => '',
                'description' => '',
            );
            $defaults['navbar_logo_height'] = '43';
            $defaults['navbar_logo_bg'] = 'rgba(255,255,255,0)';
            $defaults['navbar_link_margin'] = array(
                'border-top' => '0px',
                'border-right' => '0px',
                'border-bottom' => '0px',
                'border-left' => '0px',
                'border-style' => '',
                'border-color' => '',
            );
            $defaults['navbar_link_radius'] = array(
                'border-top' => '0px',
                'border-right' => '0px',
                'border-bottom' => '0px',
                'border-left' => '0px',
                'border-style' => '',
                'border-color' => '',
            );
            $defaults['navbar_link_transform'] = 'uppercase';
            $defaults['navbar_link'] = 'rgba(255,255,255,.6)';
            $defaults['navbar_link_hover'] = 'rgba(255,255,255,0.9)';
            $defaults['navbar_link_bg'] = 'rgba(255,255,255,0)';
            $defaults['navbar_link_bg_hover'] = 'rgba(17,17,17,0)';
            $defaults['navbar_link_hover_effect'] = 'rgba(9,225,192,1)';
            $defaults['navbar_button'] = '#ffffff';
            $defaults['navbar_button_bg'] = '#7ac8cc';
            $defaults['navbar_button_hover'] = '#ffffff';
            $defaults['navbar_button_bg_hover'] = '#7272ff';
            $defaults['navbar_link_icon'] = '#7ac8cc';
            $defaults['navbar_link_icon_hover'] = '#7272ff';
            $defaults['navbar_link_subtitle'] = '#8585bd';
            $defaults['navbar_link_subtitle_hover'] = 'rgba(255,255,255,0.9)';
            $defaults['navbar_badge'] = '#7ac8cc';
            $defaults['navbar_badge_color'] = '#ffffff';
            $defaults['sticky_background'] = 'rgba(255,255,255,0)';
            $defaults['sticky_height'] = '60';
            $defaults['sticky_logo_height'] = '25';
            $defaults['navbar_scrollbar'] = '#7ac8cc';
            $defaults['navbar_scrollbar_rail'] = '#ffffff';
            $defaults['dropdown_shadow'] = 'show';
            $defaults['dropdown_margin'] = '0';
            $defaults['dropdown_radius'] = array(
                'border-top' => '0',
                'border-right' => '0',
                'border-left' => '3',
                'border-bottom' => '3',
            );
            $defaults['dropdown_border'] = array(
                'border-top' => '3px',
                'border-right' => '',
                'border-bottom' => '',
                'border-left' => '',
                'border-style' => '',
                'border-color' => '#00A8FF',
            );
            $defaults['dropdown_background'] = '#232323';
            $defaults['dropdown_scrollbar'] = '#7ac8cc';
            $defaults['dropdown_scrollbar_rail'] = '#ffffff';
            $defaults['dropdown_title'] = '#ffffff';
            $defaults['dropdown_title_border'] = array(
                'border-top' => '3px',
                'border-right' => '',
                'border-bottom' => '',
                'border-left' => '',
                'border-style' => 'solid',
                'border-color' => '#61ffb6',
            );
            $defaults['dropdown_link'] = 'rgba(255,255,255,.6)';
            $defaults['dropdown_link_hover'] = 'rgba(255,255,255,0.9)';
            $defaults['dropdown_link_bg_hover'] = 'transparent';
            $defaults['dropdown_link_border'] = array(
                'border-top' => '0px',
                'border-right' => '0px',
                'border-bottom' => '0px',
                'border-left' => '0px',
                'border-style' => 'none',
                'border-color' => '#f4f4f4',
            );
            $defaults['dropdown_link_transform'] = 'none';
            $defaults['dropdown_button'] = '#ffffff';
            $defaults['dropdown_button_hover'] = '#ffffff';
            $defaults['dropdown_button_bg'] = '#7ac8cc';
            $defaults['dropdown_button_bg_hover'] = '#7272ff';
            $defaults['dropdown_link_icon'] = '#7ac8cc';
            $defaults['dropdown_link_icon_hover'] = '#7272ff';
            $defaults['dropdown_link_subtitle'] = '#8585bd';
            $defaults['dropdown_link_subtitle_hover'] = 'rgba(255,255,255,0.9)';
            $defaults['font'] = array(
                'font-family' => 'Open Sans',
                'font-options' => '',
                'google' => '1',
                'font-weight' => '400',
                'font-style' => '',
                'subsets' => '',
                'font-size' => '14px',
            );
            $defaults['navbar_font'] = array(
                'font-family' => 'Open Sans',
                'font-options' => '',
                'google' => '1',
                'font-weight' => '600',
                'font-style' => '',
                'subsets' => '',
                'font-size' => '16px',
            );
            $defaults['dropdown_font'] = array(
                'font-family' => 'Open Sans',
                'font-options' => '',
                'google' => '1',
                'font-weight' => '600',
                'font-style' => '',
                'subsets' => '',
                'font-size' => '16px',
            );

            return $defaults;
        }

        function defaults($defaults) {

            $defaults['integration'] = 1;
            $defaults['theme'] = 'extra';

            return $defaults;
        }

        static function activation() {

            update_option('_quadmenu_compiler', true);

            if (class_exists('QuadMenu')) {

                QuadMenu_Redux::add_notification('blue', esc_html__('Thanks for install QuadMenu Extra. We have to create the stylesheets. Please wait.', 'quadmenu-Extra'));

                QuadMenu_Activation::activation();
            }
        }

    }

    new QuadMenu_Extra();
}

if (!function_exists('is_quadmenu_installed')) {

    function is_quadmenu_installed() {

        $file_path = 'quadmenu/quadmenu.php';

        $installed_plugins = get_plugins();

        return isset($installed_plugins[$file_path]);
    }

}

register_activation_hook(__FILE__, array('QuadMenu_Extra', 'activation'));
