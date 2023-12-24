<?php
/**
 * The assets related functionality of the plugin.
 *
 * @package WPAppsDev\GSOA
 * @author Saiful Islam Ananda
 */

namespace WPAppsDev\GSOA;

use WPAppsDev\GSOA\Supports\GsheetSettings;

/**
 * Assets class
 */
class Assets {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_all_scripts' ], 10 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_front_scripts' ] );
		}
	}

	/**
	 * Register all scripts and styles.
	 */
	public function register_all_scripts() {
		$styles  = $this->get_styles();
		$scripts = $this->get_scripts();

		$this->register_styles( $styles );
		$this->register_scripts( $scripts );

		do_action( 'wpadgsoauto_register_scripts' );
	}

	/**
	 * Get registered styles.
	 *
	 * @return array
	 */
	public function get_styles() {
		$prefix = self::get_prefix();

		// All CSS file list.
		return [
			'wpadgsoauto-admin'  => [
				'src'     => WPADGSOAUTO_ASSETS . 'css/wpadgsoauto-admin.css',
				'deps'    => [],
				'version' => filemtime( WPADGSOAUTO_DIR . 'assets/css/wpadgsoauto-admin.css' ),
			],
			'wpadgsoauto-public' => [
				'src'     => WPADGSOAUTO_ASSETS . 'css/wpadgsoauto-public.css',
				'deps'    => [],
				'version' => filemtime( WPADGSOAUTO_DIR . 'assets/css/wpadgsoauto-public.css' ),
			],
			'wpadgsoauto-waitMe' => [
				'src'  => WPADGSOAUTO_ASSETS . 'lib/waitMe.min.css',
				'deps' => [],
			],
		];
	}

	/**
	 * Get all registered scripts.
	 *
	 * @return array
	 */
	public function get_scripts() {
		$prefix = self::get_prefix();

		// All JS file list.
		return [
			// Register scripts.
			'wpadgsoauto-admin'  => [
				'src'     => WPADGSOAUTO_ASSETS . "js/wpadgsoauto-admin{$prefix}.js",
				'deps'    => [ 'jquery' ],
				'version' => filemtime( WPADGSOAUTO_DIR . "assets/js/wpadgsoauto-admin{$prefix}.js" ),
			],
			'wpadgsoauto-public' => [
				'src'     => WPADGSOAUTO_ASSETS . "js/wpadgsoauto-public{$prefix}.js",
				'deps'    => [ 'jquery' ],
				'version' => filemtime( WPADGSOAUTO_DIR . "assets/js/wpadgsoauto-public{$prefix}.js" ),
			],
			'wpadgsoauto-waitMe' => [
				'src'  => WPADGSOAUTO_ASSETS . 'lib/waitMe.min.js',
				'deps' => [ 'jquery' ],
				// 'version'   => filemtime( WPADGSOAUTO_DIR . "assets/js/waitMe.min.js" ),
			],
		];
	}

	/**
	 * Get file prefix.
	 *
	 * @return string
	 */
	public static function get_prefix() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '';
	}

	/**
	 * Register scripts.
	 *
	 * @param array $scripts Registered js array.
	 *
	 * @return void
	 */
	public function register_scripts( $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
			$version   = isset( $script['version'] ) ? $script['version'] : WPADGSOAUTO_VERSION;

			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles.
	 *
	 * @param array $styles Registered stylesheet array.
	 *
	 * @return void
	 */
	public function register_styles( $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps    = isset( $style['deps'] ) ? $style['deps'] : false;
			$version = isset( $style['version'] ) ? $style['version'] : WPADGSOAUTO_VERSION;

			wp_register_style( $handle, $style['src'], $deps, $version );
		}
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_admin_scripts() {
		$default_script = [
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'wpadgsoauto-admin-security' ),
			'is_set_label' => GsheetSettings::is_set_sheet_label(),
		];

		$localize_data = apply_filters( 'wpadgsoauto_admin_localized_args', $default_script );

		// Enqueue scripts.
		wp_enqueue_script( 'wpadgsoauto-admin' );
		wp_localize_script( 'wpadgsoauto-admin', 'wpadgsoauto_admin', $localize_data );
		wp_enqueue_script( 'wpadgsoauto-waitMe' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Enqueue Styles.
		wp_enqueue_style( 'wpadgsoauto-admin' );
		wp_enqueue_style( 'wpadgsoauto-waitMe' );

		do_action( 'wpadgsoauto_enqueue_admin_scripts' );
	}

	/**
	 * Enqueue front-end scripts.
	 */
	public function enqueue_front_scripts() {
		do_action( 'wpadgsoauto_before_public_enqueue' );

		$default_script = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wpadgsoauto-security' ),
		];

		// Front end localize data.
		$localize_data = apply_filters( 'wpadgsoauto_public_localized_args', $default_script );

		// Enqueue scripts.
		wp_enqueue_script( 'wpadgsoauto-public' );
		wp_localize_script( 'wpadgsoauto-public', 'wpadgsoauto_public', $localize_data );

		// Enqueue Styles.
		wp_enqueue_style( 'wpadgsoauto-public' );

		do_action( 'wpadgsoauto_after_public_enqueue' );
	}
}
