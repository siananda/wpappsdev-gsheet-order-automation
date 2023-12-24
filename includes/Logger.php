<?php
/**
 * The log related functionality of the plugin.
 *
 * @package WPAppsDev\GSOA
 * @author Saiful Islam Ananda
 */
namespace WPAppsDev\GSOA;

/**
 * Log all things!
 */
class Logger {
	/**
	 * Hold WC logger class object.
	 *
	 * @var object
	 */
	public static $logger;

	/**
	 * Log file name
	 */
	const LOG_FILENAME = 'gsheets-log';

	/**
	 * Start log.
	 *
	 * @return void
	 */
	public static function start() {
		$formatted_time = self::get_time();
		$log_entry      = '==== Start Log ' . $formatted_time . '====';

		self::log( $log_entry );
	}

	/**
	 * End log.
	 *
	 * @return void
	 */
	public static function end() {
		$formatted_time = self::get_time();
		$log_entry      = '==== End Log ' . $formatted_time . '====';

		self::log( $log_entry );
	}

	/**
	 * Check debug log enable or not.
	 *
	 * @return bool
	 */
	public static function is_enable() {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return false;
		}

		if ( ! WP_DEBUG ) {
			return false;
		}

		return true;
	}

	/**
	 * Get WC logger class object.
	 *
	 * @return object
	 */
	public static function get_logger() {
		if ( empty( self::$logger ) ) {
			self::$logger = wc_get_logger();
		}

		return self::$logger;
	}

	/**
	 * Get Formatted Time
	 *
	 * @return string
	 */
	public static function get_time() {
		$time           = time();
		$formatted_time = date_i18n( get_option( 'date_format' ) . ' g:ia', $time );

		return $formatted_time;
	}

	/**
	 * Utilize WC logger class.
	 *
	 * @param mixed $log_entry Log data.
	 *
	 * @return void
	 */
	public static function log( $log_entry ) {
		if ( ! self::is_enable() ) {
			return;
		}

		$logger = self::get_logger();
		$logger->debug( $log_entry, [ 'source' => self::LOG_FILENAME ] );
	}
}
