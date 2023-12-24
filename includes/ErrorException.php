<?php
/**
 * The error exception related functionality of the plugin.
 *
 * @package WPAppsDev\GSOA
 * @author Saiful Islam Ananda
 */

namespace WPAppsDev\GSOA;

use Exception;

/**
 * Error exception handel class.
 */
class ErrorException extends Exception {
	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $error_code = '';

	/**
	 * Class constructor.
	 *
	 * @param int    $error_code Error code.
	 * @param string $message Error message.
	 * @param int    $status_code Status code.
	 */
	public function __construct( $error_code, $message = '', $status_code = 422 ) {
		$this->error_code = $error_code;

		parent::__construct( $message, $status_code );
	}

	/**
	 * Get error code
	 *
	 * @return string
	 */
	final public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * Get error message
	 *
	 * @return string
	 */
	final public function get_message() {
		return $this->getMessage();
	}

	/**
	 * Get error status code
	 *
	 * @return int
	 */
	final public function get_status_code() {
		return $this->getCode();
	}
}
