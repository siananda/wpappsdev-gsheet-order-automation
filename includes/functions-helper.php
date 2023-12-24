<?php

use WPAppsDev\GSOA\Supports\GsheetSettings;

/**
 * Helper functions for this plugins.
 *
 * @since 1.0.0
 */

// File Security Check.
defined( 'ABSPATH' ) || exit;

/**
 * Print debug Data.
 *
 * @param array/string $data Debug data.
 *
 * @return void
 */
function wpadgsoauto_print( $data ) {
	if ( ! WP_DEBUG ) {
		return;
	}

	echo '<pre>';

	if ( is_array( $data ) || is_object( $data ) ) {
		print_r( $data ); // phpcs:ignore
	} else {
		echo sanitize_text_field( $data ); // phpcs:ignore
	}
	echo '</pre>';
}

/**
 * Get allowed html for wp_kses.
 *
 * @return array
 */
function wpadgsoauto_allowed_html() {
	return [
		'img'    => [
			'width'   => [],
			'height'  => [],
			'src'     => [],
			'class'   => [],
			'srcset'  => [],
			'sizes'   => [],
			'loading' => [],
		],
		'option' => [
			'value'    => [],
			'selected' => [],
		],
		'a'      => [
			'target' => [],
			'href'   => [],
		],
	];
}

/**
 * Get settings option value.
 *
 * @param string $option Option key.
 * @param string $section Section key.
 * @param string $default Default value.
 *
 * @return mixed
 */
function wpadgsoauto_get_option( $option, $section, $default = '' ) {
	$options = get_option( $section );

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}

	return $default;
}

/**
 * Get post meta value.
 *
 * @param int    $pid Post id.
 * @param string $pkey Meta key.
 * @param mixed  $default Default value.
 *
 * @return mixed
 */
function wpadgsoauto_meta( $pid, $pkey, $default = '' ) {
	$value = get_post_meta( $pid, $pkey, true );

	if ( isset( $value ) && ! empty( $value ) ) {
		$option = stripslashes_deep( $value );
	} else {
		$option = $default;
	}

	return $option;
}

/**
 * Get other templates passing attributes and including the file.
 *
 * Search for the template and include the file.
 *
 * @see wpadgsoauto_locate_template()
 *
 * @param string $template_name template to load.
 * @param array  $args          args (optional) Passed arguments for the template file.
 * @param string $template_path (optional) Path to templates.
 * @param string $default_path  (optional) Default path to template files.
 */
function wpadgsoauto_get_template( $template_name, $args = [], $template_path = '', $default_path = '' ) {
	$cache_key = sanitize_key( implode( '-', [ 'template', $template_name, $template_path, $default_path, WPADGSOAUTO_VERSION ] ) );
	$template  = (string) wp_cache_get( $cache_key, WPADGSOAUTO_NAME );

	if ( ! $template ) {
		$template = wpadgsoauto_locate_template( $template_name, $template_path, $default_path );
		wp_cache_set( $cache_key, $template, WPADGSOAUTO_NAME );
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$filter_template = apply_filters( 'wpadgsoauto_get_template', $template, $template_name, $args, $template_path, $default_path );

	if ( $filter_template !== $template ) {
		if ( ! file_exists( $filter_template ) ) {
			/* translators: %s template */
			wpadgsoauto_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'wpappsdev-pcbuilder' ), '<code>' . $template . '</code>' ), '1.0.0' );

			return;
		}
		$template = $filter_template;
	}

	$action_args = [
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	];

	if ( ! empty( $args ) && is_array( $args ) ) {
		if ( isset( $args['action_args'] ) ) {
			wpadgsoauto_doing_it_wrong(
				__FUNCTION__,
				__( 'action_args should not be overwritten when calling wpadgsoauto_get_template.', 'wpappsdev-pcbuilder' ),
				'1.0.0'
			);
			unset( $args['action_args'] );
		}
		extract( $args ); // @codingStandardsIgnoreLine
	}

	do_action( 'wpadgsoauto_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

	if ( file_exists( $action_args['located'] ) ) {
		include $action_args['located'];
	}

	do_action( 'wpadgsoauto_after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}

/**
 * Like wpadgsoauto_get_template, but returns the HTML instead of outputting.
 *
 * @see wpadgsoauto_get_template
 *
 * @param string $template_name template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function wpadgsoauto_get_template_html( $template_name, $args = [], $template_path = '', $default_path = '' ) {
	ob_start();
	wpadgsoauto_get_template( $template_name, $args, $template_path, $default_path );

	return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * Locate the called template.
 * Search Order:
 * 1. /themes/theme-name/plugins-name/$template_name
 * 2. /plugins/plugins-name/partials/templates/$template_name.
 *
 * @author Saiful Islam
 *
 * @param string $template_name template to load.
 * @param string $template_path (optional) Path to templates.
 * @param string $default_path  (optional) Default path to template files.
 *
 * @return string $template path to the template file.
 */
function wpadgsoauto_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	// Set variable to search in templates folder of theme.
	if ( ! $template_path ) {
		$template_path = get_template_directory() . '/' . WPADGSOAUTO_NAME . '/';
	}

	// Set default plugin templates path.
	if ( ! $default_path ) {
		$default_path = WPADGSOAUTO_DIR . 'templates/';
	}
	// Search template file in theme folder.
	$template = locate_template( [ $template_path . $template_name, $template_name ] );

	// Get plugins template file.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	return apply_filters( 'wpadgsoauto_locate_template', $template, $template_name, $template_path, $default_path );
}

/**
 * Wrapper for wpadgsoauto_doing_it_wrong.
 *
 * @author Saiful Islam
 *
 * @param string $function function used.
 * @param string $message  message to log.
 * @param string $version  version the message was added in.
 */
function wpadgsoauto_doing_it_wrong( $function, $message, $version ) {
	// @codingStandardsIgnoreStart
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( is_ajax() ) {
		do_action( 'wpadgsoauto_doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
	// @codingStandardsIgnoreEnd
}

/**
 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
 * to the end of the array.
 *
 * @param array  $array Main array.
 * @param string $key Array key index.
 * @param array  $new New array.
 *
 * @return array
 */
function wpadgsoauto_array_insert_after( array $array, string $key, array $new ) {
	$keys  = array_keys( $array );
	$index = array_search( $key, $keys ); // phpcs:ignore
	$pos   = false === $index ? count( $array ) : $index + 1;

	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}

/**
 * Check repeatable field empty or not.
 *
 * @param mixed $data Repeatable fields data.
 *
 * @return bool
 */
function wpadgsoauto_is_repeatable_empty( $data ) {
	if ( ! is_array( $data ) || 0 === count( $data ) ) {
		return true;
	}

	if ( 1 === count( $data ) ) {
		foreach ( $data[0] as $key => $value ) {
			if ( '' !== trim( $value ) ) {
				return false;
			}
		}

		return true;
	}

	return false;
}

/**
 * Generate spreadsheets work sheet title.
 *
 * @return string
 */
function wpadgsoauto_get_sheet_title() {
	return apply_filters( 'wpadgsoauto_work_sheet_title', 'Year' . gmdate( 'Y' ) );
}

/**
 * Convert color hex value to rgb value.
 *
 * @param string $hex_value Color HEX value.
 *
 * @return array
 */
function wpadgsoauto_convert_hex_to_rgb( $hex_value ) {
	$trimmed_string                = ltrim( $hex_value, '#' );
	list( $ired, $igreen, $iblue ) = array_map( 'hexdec', str_split( $trimmed_string, 2 ) );

	return [ $ired, $igreen, $iblue ];
}

/**
 * Google sheets columns data list.
 *
 * @return array
 */
function wpadgsoauto_gsheet_columns() {
	$gsheet_columns = [
		'order_date'    => __( 'Order Date', 'wpappsdev-gsheet-order-automation' ),
		'customer_name' => __( 'Customer Name', 'wpappsdev-gsheet-order-automation' ),
		'order_number'  => __( 'Order number', 'wpappsdev-gsheet-order-automation' ),
		'address'       => __( 'Customer Address', 'wpappsdev-gsheet-order-automation' ),
		'products'      => __( 'Products', 'wpappsdev-gsheet-order-automation' ),
		'order_total'   => __( 'Order Total', 'wpappsdev-gsheet-order-automation' ),
	];

	return apply_filters( 'wpadgsoauto_google_sheet_columns_info', $gsheet_columns );
}

/**
 * Reset google sheet configuration.
 *
 * @return void
 */
function wpadgsoauto_reset_gsheet_config() {
	// Remove Spreadsheet ID and URL.
	GsheetSettings::set_spreadsheets_id( '' );
	GsheetSettings::set_spreadsheets_url( '' );
	GsheetSettings::set_sheets_columns_data( [] );
	GsheetSettings::set_is_set_sheet_label( false );
}
