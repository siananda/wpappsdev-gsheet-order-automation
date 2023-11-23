<?php

namespace  WPAppsDev\GSOA\Api;

use Exception;
use Google\Service\Drive;
use WPAppsDev\GSOA\Logger;
use WPAppsDev\GSOA\Supports\GsheetApi as Api;

/**
 * The drives class.
 */
class Drives extends Api {
	/**
	 * Retrieves a file.
	 *
	 * @param string $file_id File id.
	 * @param array  $options Extra options.
	 *
	 * @return object|false
	 */
	public static function get( $file_id, $options = [] ) {
		try {
			$service = self::drives();

			return $service->files->get( $file_id, $options );
		} catch ( Exception $e ) {
			Logger::log( sprintf( 'Could not retrieve file for id: %1$s. Error: %2$s', $file_id, $e->getMessage() ) );

			return false;
		}
	}

	/**
	 * Delete a file.
	 *
	 * @param string $file_id File id.
	 * @param array  $options Extra options.
	 *
	 * @return bool
	 */
	public static function delete( $file_id, $options = [] ) {
		try {
			$service = self::drives();
			$service->files->delete( $file_id, $options );

			return true;
		} catch ( Exception $e ) {
			Logger::log( sprintf( 'Could not delete file for id: %1$s. Error: %2$s', $file_id, $e->getMessage() ) );

			return false;
		}
	}

	/**
	 * Initialize google drive service object.
	 *
	 * @return object
	 */
	private static function drives() {
		$client = self::api();

		return new Drive( $client );
	}
}
