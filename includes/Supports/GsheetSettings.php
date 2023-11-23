<?php

namespace WPAppsDev\GSOA\Supports;

defined( 'ABSPATH' ) || exit; // Exit if called directly.

/**
 * Gsheet settings handler class
 */
class GsheetSettings {
	/**
	 * Retrieves gsheet settings option
	 *
	 * @param string $key Setting option key.
	 *
	 * @return mixed
	 */
	public static function get( $key = '' ) {
		if ( empty( $key ) ) {
			return '';
		}

		return get_option( $key, '' );
	}

	/**
	 * Set gsheet settings option
	 *
	 * @param string $key Settings key.
	 * @param mixed  $value Settings value.
	 *
	 * @return void
	 */
	public static function update( $key, $value ) {
		update_option( $key, $value );
	}

	/**
	 * Checks if synchronization is enabled
	 *
	 * @return bool
	 */
	public static function is_enable() {
		return 'yes' === self::get( 'wpadgsoauto_enable_synchronize' );
	}

	/**
	 * Checks if debugging is enabled.
	 *
	 * @return bool
	 */
	public static function is_debug_mode() {
		return 'yes' === self::get( 'mplusdsc_enable_debug' );
	}

	/**
	 * Retrieves client id.
	 *
	 * @return string
	 */
	public static function get_client_id() {
		return self::get( 'wpadgsoauto_google_client_id' );
	}

	/**
	 * Retrieves client secret key.
	 *
	 * @return string
	 */
	public static function get_client_secret() {
		return self::get( 'wpadgsoauto_google_client_secret' );
	}

	/**
	 * Retrieves spreadsheets id.
	 *
	 * @return string
	 */
	public static function get_spreadsheets_id() {
		return self::get( 'wpadgsoauto_google_spreadsheets_id' );
	}

	/**
	 * Retrieves spreadsheets url.
	 *
	 * @return string
	 */
	public static function get_spreadsheets_url() {
		return self::get( 'wpadgsoauto_google_spreadsheets_url' );
	}

	/**
	 * Retrieves sheets columns data.
	 *
	 * @return string
	 */
	public static function get_sheets_columns_data() {
		return self::get( 'wpadgsoauto_sheets_columns_data' );
	}

	/**
	 * Retrieves access token.
	 *
	 * @return string
	 */
	public static function get_access_token() {
		return self::get( 'wpadgsoauto_access_token' );
	}

	/**
	 * Retrieves refresh token.
	 *
	 * @return string
	 */
	public static function get_refresh_token() {
		return self::get( 'wpadgsoauto_refresh_token' );
	}

	/**
	 * Retrieves label background color.
	 *
	 * @return string
	 */
	public static function get_label_bg_color() {
		return self::get( 'wpadgsoauto_spreadsheets_bg_color' );
	}

	/**
	 * Retrieves label text color.
	 *
	 * @return string
	 */
	public static function get_label_text_color() {
		return self::get( 'wpadgsoauto_spreadsheets_fg_color' );
	}

	/**
	 * Retrieves redirect url.
	 *
	 * @return string
	 */
	public static function get_redirect_uri() {
		return admin_url( 'admin.php?page=wc-settings&tab=wpadgsoauto-sheets' );
	}

	/**
	 * Check if sheet label set.
	 *
	 * @return bool
	 */
	public static function is_set_sheet_label() {
		return ( (int) self::get( 'wpadgsoauto_is_set_sheet_label' ) === 1 );
	}

	/**
	 * Set access token.
	 *
	 * @param mixed $value Settings value.
	 *
	 * @return string
	 */
	public static function set_access_token( $value ) {
		return self::update( 'wpadgsoauto_access_token', $value );
	}

	/**
	 * Set refresh token.
	 *
	 * @param mixed $value Settings value.
	 *
	 * @return string
	 */
	public static function set_refresh_token( $value ) {
		return self::update( 'wpadgsoauto_refresh_token', $value );
	}

	/**
	 * Set spreadsheet id.
	 *
	 * @param mixed $value Settings value.
	 *
	 * @return string
	 */
	public static function set_spreadsheets_id( $value ) {
		return self::update( 'wpadgsoauto_google_spreadsheets_id', $value );
	}

	/**
	 * Set spreadsheet url.
	 *
	 * @param mixed $value Settings value.
	 *
	 * @return string
	 */
	public static function set_spreadsheets_url( $value ) {
		return self::update( 'wpadgsoauto_google_spreadsheets_url', $value );
	}

	/**
	 * Update is_set_sheet_label value
	 *
	 * @param mixed $value Settings value.
	 *
	 * @return bool
	 */
	public static function set_is_set_sheet_label( $value ) {
		return self::update( 'wpadgsoauto_is_set_sheet_label', $value );
	}

	/**
	 * Sets sheets columns data.
	 *
	 * @param mixed $value Settings value.
	 *
	 * @return string
	 */
	public static function set_sheets_columns_data( $value ) {
		return self::update( 'wpadgsoauto_sheets_columns_data', $value );
	}
}
