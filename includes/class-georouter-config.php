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

    public function render_settings_page()
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
        <br />
        <hr />
        <h3>Shortcodes</h3>
        <p>
            <span>You can load in the <b>GeoRouter Region Switcher/Dropdown</b> using the shortcode: </span>
            <input type="text" readonly value="[georouter-switcher]" />
        </p>
        <br />
        <hr />
        <p><a href="https://wordpress.org/support/view/plugin-reviews/georouter?rate=5#postform" target="_blank" rel="noopener noreferrer">Love this plugin? Give our WordPress Plugin your rating :)</a></p>
<?php
    }

    public function register_and_build_fields()
    {
        register_setting(
            $this->id . '_settings',
            $this->id . '_settings'
        );
        add_settings_section(
            'georouter_section_one',
            '',
            '__return_empty_string',
            $this->id
        );
        add_settings_field(
            'enable_disable',
            'Enable GeoRouter',
            array($this, 'georouter_render_enable_disable_checkbox_field'),
            $this->id,
            'georouter_section_one'
        );
        add_settings_field(
            'disable_for_user_roles',
            'Disable for User Roles',
            array($this, 'georouter_render_disable_for_roles_field'),
            $this->id,
            'georouter_section_one'
        );
        add_settings_field(
            'namespace',
            'GeoRouter Namespace',
            array($this, 'georouter_render_namespace_text_field'),
            $this->id,
            'georouter_section_one'
        );
        add_settings_field(
            'location',
            'Where would you like to load the script (default: before <head/>)',
            array($this, 'georouter_render_location_dropdown_field'),
            $this->id,
            'georouter_section_one'
        );
    }

    public function georouter_render_enable_disable_checkbox_field()
    {
        printf(
            '<input type="checkbox" name="%s" %s />',
            esc_attr($this->id . '_settings[enable_disable]'),
            esc_attr($this->is_enabled() ? 'checked' : '')
        );
    }

    public function georouter_render_namespace_text_field()
    {
        printf(
            '<input type="text" name="%s" value="%s" />',
            esc_attr($this->id . '_settings[namespace]'),
            esc_attr($this->get_namespace())
        );
    }

    public function georouter_render_location_dropdown_field()
    {
        $location = $this->get_location();
        printf(
            '<select name="%s"><option value="head" %s>%s</option><option value="body" %s>%s</option></select>',
            esc_attr($this->id . '_settings[location]'),
            esc_attr(($location === 'head' || empty($location)) ? 'selected' : ''),
            esc_html__('before </head>'),
            esc_attr($this->get_location() === 'body' ? 'selected' : ''),
            esc_html__('before </body>')
        );
    }

    public function georouter_render_disable_for_roles_field()
    {
        $user_roles = get_editable_roles();
        $disabled_user_roles = $this->get_disabled_user_roles();
        $output = "<div>";
        foreach ($user_roles as $user_role) {
            $user_role_name = $user_role['name'];
            $user_role_key = strtolower($user_role_name);
            $checkbox_id = 'disable_for_user_roles__' . $user_role_key;
            $output .= sprintf(
                '<label for="%s">%s <input type="checkbox" name="%s" id="%s" %s />&nbsp;&nbsp;&nbsp;</label>',
                esc_attr($checkbox_id),
                esc_attr($user_role_name),
                esc_attr($this->id . '_settings[disable_for_user_roles][' . $user_role_key . ']'),
                esc_attr($checkbox_id),
                esc_attr(in_array($user_role_key, $disabled_user_roles) ? 'checked' : '')
            );
        }
        $output .= "</div>";
        echo $output;
    }

    public function get_namespace()
    {
        return get_option($this->id . '_settings')['namespace'];
    }
    public function get_location()
    {
        return get_option($this->id . '_settings')['location'];
    }
    public function get_disabled_user_roles()
    {
        $options = get_option($this->id . '_settings');
        if (array_key_exists('disable_for_user_roles', $options)) {
            $disabled_user_roles = [];
            foreach ($options['disable_for_user_roles'] as $key => $value) {
                array_push($disabled_user_roles, $key);
            }
            return $disabled_user_roles;
        }
        return [];
    }
    public function is_enabled($check_disabled_roles = true)
    {
        $options = get_option($this->id . '_settings');
        if (array_key_exists('enable_disable', $options)) {
            if ($options['enable_disable'] === 'on') {
                if ($check_disabled_roles) {
                    // Check current user logged in
                    // If so, check role to ensure it's not under disabled_for_user_role
                    $user = wp_get_current_user();
                    if (!empty($user->ID)) {
                        $disabled_user_roles = $this->get_disabled_user_roles();
                        foreach ($disabled_user_roles as $role) {
                            if (in_array($role, (array) $user->roles)) {
                                return false;
                            }
                        }
                    }
                }
                return true;
            }
        }
        return false;
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
