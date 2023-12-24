<?php

namespace WPAppsDev\GSOA\Supports;

defined( 'ABSPATH' ) || exit;

use Exception;
use Gsheet\Gsheet;
use WPAppsDev\GSOA\Traits\ChainableContainer;
use Google\Service\Drive;
use Google\Service\Sheets;

/**
 * Configuration handler class
 */
class GsheetConfig {
	use ChainableContainer;

	/**
	 * Gsheet app name
	 *
	 * @var string
	 */
	private static $app_name = 'Google Sheets WooCommerce Integration';

	/**
	 * Determines if configuration is loaded
	 *
	 * @var bool
	 */
	private $api_configured = false;

	/**
	 * Determines if configuration has error
	 *
	 * @var bool
	 */
	private $api_error;

	/**
	 * The reference to Singleton instance of this class
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Hold Google\Client class object.
	 *
	 * @var object
	 */
	public $client = null;

	/**
	 * Private constructor to prevent creating a new instance of the
	 * Singleton via the `new` operator from outside of this class.
	 *
	 * @return void
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Retrieves the singletone instance of the class.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Initializes the configuration
	 *
	 * @return object
	 */
	private function init() {
		if ( $this->is_ready_for_oauth() ) {
			try {
				$this->set_google_client();
				$this->set_controllers();
				$this->api_configured = true;
			} catch ( Exception $e ) {
				$this->api_configured = false;
				$this->api_error      = $e->getMessage();
			}
		}

		return $this;
	}

	/**
	 * Sets API configuration for Gsheet
	 *
	 * @return void
	 */
	private function set_google_client() {
		$this->client = new \Google\Client();
		$this->client->setApplicationName( self::$app_name );
		$this->client->setScopes( self::get_scopes() );
		$this->client->setClientId( self::get_client_id() );
		$this->client->setClientSecret( self::get_client_secret() );
		$this->client->setRedirectUri( self::get_redirect_uri() );
		$this->client->setAccessType( 'offline' );
		$this->client->setApprovalPrompt( 'force' );
		$this->client->setPrompt( 'consent' );
		$this->client->setAccessToken( self::get_access_token() );
	}

	/**
	 * Sets controller class objects.
	 *
	 * @return void
	 */
	private function set_controllers() {
		$this->container['client'] = $this->client;
	}

	/**
	 * Retrieves google auth client scopes.
	 *
	 * @return array
	 */
	public static function get_scopes() {
		return [
			/** See, edit, create, and delete all of your Google Drive files. */
			Drive::DRIVE,
			/** See, edit, create, and delete only the specific Google Drive files you use with this app. */
			Drive::DRIVE_FILE,
			/** See and download all your Google Drive files. */
			Drive::DRIVE_READONLY,
			/** See, create, and delete its own configuration data in your Google Drive. */
			Drive::DRIVE_APPDATA,
			/** See, edit, create, and delete all your Google Sheets spreadsheets. */
			Sheets::SPREADSHEETS,
			/** See all your Google Sheets spreadsheets. */
			Sheets::SPREADSHEETS_READONLY,
		];
	}

	/**
	 * Verifies if api information are set properly.
	 *
	 * @return bool
	 */
	public static function is_ready_for_oauth() {
		if ( ! self::get_client_secret() || ! self::get_client_id() ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves client secret key.
	 *
	 * @return string
	 */
	public static function get_client_secret() {
		return trim( GsheetSettings::get_client_secret() );
	}

	/**
	 * Retrieves client id.
	 *
	 * @return string
	 */
	public static function get_client_id() {
		return trim( GsheetSettings::get_client_id() );
	}

	/**
	 * Retrieves refresh token.
	 *
	 * @return string
	 */
	public static function get_refresh_token() {
		return GsheetSettings::get_refresh_token();
	}

	/**
	 * Retrieves access token.
	 *
	 * @return string
	 */
	public static function get_access_token() {
		return GsheetSettings::get_access_token();
	}

	/**
	 * Retrieves oauth redirect url.
	 *
	 * @return string
	 */
	public static function get_redirect_uri() {
		return GsheetSettings::get_redirect_uri();
	}
}
