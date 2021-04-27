<?php

defined('ABSPATH') || exit;

class Georouter_Config
{
    public $Georouter;

    public static $_id = 'georouter';
    public static $_label = 'GeoRouter';
    public static $_description = 'Auto-redirect website visitors using IP geolocation technology. Configure your routing by continent, country, state & city.';
    public static $_website = 'https://www.webdoodle.com.au/georouter/?utm_source=wordpress&utm_medium=plugin&utm_campaign=georouter';

    public function __construct(Georouter $Georouter)
    {
        $this->Georouter = $Georouter;
        $this->id = self::$_id;
        $this->label = self::$_label;
        $this->settings_description = self::$_description;
        $this->settings_website = self::$_website;
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'init_settings_page'), 99);
        add_action('admin_init', array($this, 'register_and_build_fields'));
    }

    public function init_settings_page()
    {
        add_options_page(
            $this->label,
            'GeoRouter',
            'manage_options',
            $this->id,
            array($this, 'render_settings_page')
        );
    }

    public function display_settings_page()
    {
?>
        <h2>GeoRouter Settings</h2>
        <p><?php echo sprintf(__('<a href="%s" target="_blank" rel="noopener noreferrer">Don\'t have your Web Doodle GeoRouter Namespace?</a>'), $this->settings_website); ?></p>
        <form action="options.php" method="post">
            <?php
            settings_fields($this->id . '_settings');
            do_settings_sections($this->id);
            ?>
            <input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e('Save'); ?>" />
        </form>
        <p>
            <span>You can load in the GeoRouter Region Switcher/Dropdown using the shortcode: </span>
            <input type="text" readonly value="[georouter-switcher]" />
        </p>
<?php
    }

    public function register_and_build_fields()
    {
        register_setting(
            $this->id . '_settings',
            $this->id . '_settings'
        );
        add_settings_field(
            'enable_disable',
            'Enable/Disable',
            array($this, 'georouter_render_enable_disable_checkbox_field'),
            $this->id
        );
        add_settings_field(
            'namespace',
            'Input your GeoRouter Namespace',
            array($this, 'georouter_render_namespace_text_field'),
            $this->id
        );
        add_settings_field(
            'location',
            'Where would you like to load the script (default: before <head/>)',
            array($this, 'georouter_render_location_dropdown_field'),
            $this->id
        );
    }

    public function georouter_render_enable_disable_checkbox_field()
    {
        printf(
            '<input type="checkbox" name="%s" %s />',
            esc_attr('georouter_settings[enable_disable]'),
            esc_attr($this->is_enabled() ? 'checked' : 'false')
        );
    }

    public function georouter_render_namespace_text_field()
    {
        printf(
            '<input type="text" name="%s" value="%s" />',
            esc_attr('georouter_settings[namespace]'),
            esc_attr($this->get_namespace())
        );
    }

    public function georouter_render_location_dropdown_field()
    {
        printf(
            '<select name="%"><option value="head" %s>before %s</option><option value="body" %s>before %s</option></select>',
            esc_attr('georouter_settings[namespace]'),
            $this->get_location() === 'head' ? 'selected' : '',
            esc_html__('</head>'),
            $this->get_location() === 'body' ? 'selected' : '',
            esc_html__('</body>')
        );
    }

    public function get_namespace()
    {
        return get_option('georouter_settings')['namespace'];
    }
    public function get_location()
    {
        return get_option('georouter_settings')['location'];
    }
    public function is_enabled()
    {
        return get_option('georouter_settings')['enable_disable'] === 'yes';
    }

    public static function get_id()
    {
        return self::$_id;
    }
    public static function get_label()
    {
        return self::$_label;
    }
    public static function get_description()
    {
        return self::$_description;
    }
    public static function get_website_url()
    {
        return self::$_website;
    }
}
