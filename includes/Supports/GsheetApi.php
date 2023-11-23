<?php

namespace WPAppsDev\GSOA\Supports;

defined( 'ABSPATH' ) || exit; // Exit if called directly.

/**
 * API handler class
 */
class GsheetApi {
	/**
	 * Retrieves the desired API object.
	 *
	 * @return \Gsheet\GsheetClient
	 */
	protected static function api() {
		return self::config()->client;
	}

	/**
	 * Returns instance of configuration.
	 *
	 * @return Config
	 */
	protected static function config() {
		return GsheetConfig::instance();
	}

	/**
	 * Check in google client ready for oauth.
	 *
	 * @return bool
	 */
	public static function is_ready_for_oauth() {
		return self::config()->is_ready_for_oauth();
	}

	/**
	 * Check is token expired or not expired.
	 *
	 * @return bool
	 */
	public static function is_token_expired() {
		if ( is_null( self::api() ) ) {
			return false;
		}

		if ( self::api()->isAccessTokenExpired() ) {
			self::refresh_access_token();

			if ( self::api()->isAccessTokenExpired() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Refresh the token if possible, else fetch a new one
	 *
	 * @return void
	 */
	public static function refresh_access_token() {
		// Refresh the token if possible, else fetch a new one.
		if ( self::api()->getRefreshToken() ) {
			self::api()->fetchAccessTokenWithRefreshToken( self::api()->getRefreshToken() );
			$access_token = self::api()->getAccessToken();

			if ( ! self::api()->isAccessTokenExpired() ) {
				if ( is_array( $access_token ) && isset( $access_token['access_token'] ) ) {
					self::save_access_token( $access_token );
				}
			}
		}
	}

	/**
	 * Save access token.
	 *
	 * @param mixed $access_token Access token info.
	 *
	 * @return void
	 */
	public static function save_access_token( $access_token ) {
		GsheetSettings::set_access_token( $access_token );
	}
}
