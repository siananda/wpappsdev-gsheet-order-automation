<?php

namespace  WPAppsDev\GSOA\Api;

use WPAppsDev\GSOA\Supports\GsheetApi as Api;

/**
 * The auth class.
 */
class Auth extends Api {
	/**
	 * Retrieves google auth url.
	 *
	 * @return string
	 */
	public static function auth_url() {
		return self::api()->createAuthUrl();
	}

	/**
	 * Generate google oauth token from code.
	 *
	 * @param string $code Access token code.
	 *
	 * @return array
	 */
	public static function get_oauth_token( $code ) {
		$client = self::api();
		// Try to get an access token using the authorization code grant.
		$access_token = $client->fetchAccessTokenWithAuthCode( $code );

		if ( is_array( $access_token ) && isset( $access_token['access_token'] ) ) {
			self::save_access_token( $access_token );
		}

		return $access_token;
	}

	/**
	 * Retrieves GsheetConfig configuration instance.
	 *
	 * @return object
	 */
	public static function get_config() {
		return self::config();
	}

	/**
	 * Retrieves google api client object.
	 *
	 * @return object
	 */
	public static function get_api() {
		return self::api();
	}
}
