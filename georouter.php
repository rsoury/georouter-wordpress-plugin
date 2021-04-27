<?php

/**
 * Plugin Name:       GeoRouter
 * Plugin URI:        https://wordpress.org/plugins/georouter/
 * Description:       Auto-redirect website visitors using IP geolocation technology. Configure your routing by continent, country, state & city.
 * Version:           0.0.1
 * Author:            Web Doodle
 * Author URI:        https://www.webdoodle.com.au/
 * License:           GPL-2.0+
 * Github URI:        https://github.com/rsoury/georouter-wordpress-plugin
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 *
 * @version  0.0.1
 * @package  GeoRouter
 * @author   Web Doodle
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class Georouter
{
	/* version number */
	const VERSION = '0.0.1';
	/* api */
	const JS_URL = 'https://georouter.services.webdoodle.com.au/';
	const DEV_JS_URL = 'https://georouter.dev-services.webdoodle.com.au/';

	/** @var \Georouter single instance of this plugin */
	protected static $instance;

	public $Georouter_Config;

	public function __construct()
	{
		add_action('plugins_loaded', array($this, 'initialize'), 100);
	}

	public function initialize()
	{
		$this->load_dependencies();

		// The earlier we handle these, the better. This way we have access to our public vars.
		// Handle Settings Tab
		$this->handle_config();

		// Setup plugin action links -- see plugin page.
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		// Shortcode for Dropdown/Switcher widget
		add_shortcode('georouter-switcher', array($this, 'georouter_switcher_shortcode'));
	}

	public function basename()
	{
		return plugin_basename(__FILE__);
	}

	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load all dependency files for this plugin.
	 *
	 */
	public function load_dependencies()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-georouter-config.php';
		require_once plugin_dir_path(__FILE__) . 'includes/class-georouter-util.php';
	}

	/**
	 * Adds plugin action links.
	 *
	 */
	public function plugin_action_links($links)
	{
		$plugin_links = array(
			'<a href="' . admin_url() . 'admin.php?page=' . $this->Georouter_Config->id . '">' . esc_html__('Settings', 'woocommerce') . '</a>',
			'<a href="' . $this->Georouter_Config->settings_website . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Website', 'woocommerce') . '</a>'
		);
		return array_merge($plugin_links, $links);
	}

	/**
	 * Setup and Initiate Config
	 *
	 * @return void
	 */
	public function handle_config()
	{
		$this->Georouter_Config = new Georouter_Config($this);
		$this->Georouter_Config->init();
	}

	/**
	 * Load JS Scripts into Header
	 *
	 * @return void
	 */
	public function enqueue_scripts()
	{
		$is_enabled = $this->Georouter_Config->is_enabled();
		$namespace = $this->Georouter_Config->get_namespace();
		if ($is_enabled && !empty($namespace)) {
			$in_footer = $this->Georouter_Config->get_location() === 'body';
			$script_url = self::JS_URL . trailingslashit($namespace);
			wp_enqueue_script('georouter', $script_url, array(), false, $in_footer);
		}
	}

	/**
	 * Load content for GeoRouter Switcher Shortcode
	 *
	 * @return void
	 */
	public function georouter_switcher_shortcode()
	{
?>
		<!-- WebDoodle Country Select -->
		<div class="wd-locale-select-container wd-locale wd-georouter"></div>
		<!-- /WebDoodle Country Select -->
<?php
	}
}

function wp_georouter()
{
	return Georouter::instance();
}

$GLOBALS['wp_georouter'] = wp_georouter();
