<?php
/**
 * Plugin Name:       WPAppsDev - GSheet Order Automation
 * Description:       This is a Google Sheets Integration plugins for WooCommerce.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Saiful Islam Ananda
 * Author URI:        http://saifulananda.me/
 * License:           GPL v2 or later
 * Text Domain:       wpappsdev-gsheet-order-automation
 * Domain Path:       /languages
 * WC tested up to:   8.0.3
 *
 * @package WPAppsDev\GSOA
 */

// don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPAppsDev_GoogleSheets class.
 *
 * @class WPAppsDev_GoogleSheets The class that holds the entire WPAppsDev_GoogleSheets plugin
 *
 * @since 1.0.0
 *
 * @author Saiful Islam Ananda
 */
final class WPAppsDev_GoogleSheets {
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Instance of self.
	 *
	 * @var WPAppsDev_GoogleSheets
	 */
	private static $instance = null;

	/**
	 * Holds various class instances.
	 *
	 * @var array
	 */
	private $container = [];

	/**
	 * Constructor for the WPAppsDev_GoogleSheets class.
	 *
	 * Sets up all the appropriate hooks and actions within our plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {
		require_once __DIR__ . '/vendor/autoload.php';

		$this->define_constants();

		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	/**
	 * Initializes the WPAppsDev_GoogleSheets() class.
	 *
	 * Checks for an existing WPAppsDev_GoogleSheets() instance and if it doesn't find one, creates it.
	 *
	 * @since 1.0.0
	 *
	 * @return WPAppsDev_GoogleSheets|bool
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Magic getter to bypass referencing objects.
	 *
	 * @param string $prop Object key.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}
	}

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param string $prop Object key.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
	}

	/**
	 * Define the required plugin constants.
	 *
	 * @return void
	 */
	public function define_constants() {
		$this->define( 'WPADGSOAUTO', __FILE__ );
		$this->define( 'WPADGSOAUTO_NAME', 'wpappsdev-gsheet-order-automation' );
		$this->define( 'WPADGSOAUTO_VERSION', $this->version );
		$this->define( 'WPADGSOAUTO_DIR', trailingslashit( plugin_dir_path( WPADGSOAUTO ) ) );
		$this->define( 'WPADGSOAUTO_URL', trailingslashit( plugin_dir_url( WPADGSOAUTO ) ) );
		$this->define( 'WPADGSOAUTO_ASSETS', trailingslashit( WPADGSOAUTO_URL . 'assets' ) );
	}

	/**
	 * Define constant if not already defined.
	 *
	 * @param string      $name Constant name.
	 * @param string|bool $value Constant value.
	 *
	 * @return void
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init_plugin() {
		$this->includes();
		$this->init_hooks();

		do_action( 'wpadgsoauto_loaded' );
	}

	/**
	 * Include all the required files.
	 *
	 * @return void
	 */
	public function includes() {
	}

	/**
	 * Initialize the action and filter hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		if ( ! self::check_required_plugins() ) {
			add_action( 'admin_notices', [ $this, 'required_plugin_notice' ] );

			return;
		}

		// Localize our plugin.
		add_action( 'init', [ $this, 'localization_setup' ] );

		// initialize the classes.
		add_action( 'init', [ $this, 'init_classes' ], 5 );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );
	}

	/**
	 * Initialize plugin for localization.
	 *
	 * @uses load_plugin_textdomain()
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wpappsdev-gsheet-order-automation', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Init all the classes.
	 *
	 * @return void
	 */
	public function init_classes() {
		if ( is_admin() ) {
			new \WPAppsDev\GSOA\Admin\WcSettingsFields();
			new WPAppsDev\GSOA\Admin\WcSettings();
		}

		new WPAppsDev\GSOA\WcGoogleSheet();

		$this->container['scripts'] = new WPAppsDev\GSOA\Assets();

		$this->container = apply_filters( 'wpadgsoauto_class_container', $this->container );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new WPAppsDev\GSOA\Ajax();
		}
	}

	/**
	 * Plugin settings action link.
	 *
	 * @param array $actions Actions array data.
	 *
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$actions[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=wpadgsoauto-sheets' ) ) . '">' . __( 'Configure', 'wpappsdev-gsheet-order-automation' ) . '</a>';

		return $actions;
	}

	/**
	 * Do stuff upon plugin activation.
	 *
	 * @return void
	 */
	public function activate() {
		$installed = get_option( 'wpadgsoauto_installed' );

		if ( ! $installed ) {
			update_option( 'wpadgsoauto_installed', time() );
		}

		update_option( 'wpadgsoauto_version', WPADGSOAUTO_VERSION );
	}

	/**
	 * Do stuff upon plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
	}

	/**
	 * Required plugins validation.
	 *
	 * @return bool
	 */
	public static function check_required_plugins() {
		$required_install = false;

		if ( ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$required_install = true;
		}

		if ( is_multisite() ) {
			if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				$required_install = true;
			}
		}

		return $required_install;
	}

	/**
	 * Required plugin activation notice.
	 *
	 * @return void
	 * */
	public function required_plugin_notice() {
		$core_plugin_file = 'woocommerce/woocommerce.php';

		include_once WPADGSOAUTO_DIR . 'templates/admin/admin-notice.php';
	}
}

/**
 * Initializes the main plugin.
 *
 * @return \WPAppsDev_GoogleSheets
 */
function wpadgsoauto_process() {
	return WPAppsDev_GoogleSheets::init();
}

// Lets Go....
wpadgsoauto_process();
