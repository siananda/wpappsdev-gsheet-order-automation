<?php
/**
 * The ajax related functionality of the plugin.
 *
 * @package WPAppsDev\GSOA
 * @author Saiful Islam Ananda
 */

namespace WPAppsDev\GSOA;

use WPAppsDev\GSOA\Api\SpreadSheets;
use WPAppsDev\GSOA\Supports\GsheetSettings;

/**
 * Ajax handler for this plugins.
 */
class Ajax {
	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		// Ajax functionality for create a new spreadsheet.
		add_action( 'wp_ajax_add_google_spreadsheets', [ $this, 'add_google_spreadsheets_process' ] );
		// Ajax functionality for set spreadsheet cells label.
		add_action( 'wp_ajax_set_spreadsheets_label', [ $this, 'set_spreadsheets_label_process' ] );
		// Ajax functionality for reset spreadsheet cells label.
		add_action( 'wp_ajax_gsheet_reset_label', [ $this, 'gsheet_reset_label_process' ] );
		// Ajax functionality to unlink a spreadsheet.
		add_action( 'wp_ajax_unlink_google_spreadsheets', [ $this, 'unlink_google_spreadsheets_process' ] );
		// Ajax functionality to unlink a spreadsheet.
		add_action( 'wp_ajax_gsheet_disconnect_auth', [ $this, 'gsheet_disconnect_auth_process' ] );
	}

	/**
	 * Ajax functionality for create a new spreadsheet
	 *
	 * @return void
	 */
	public function add_google_spreadsheets_process() {
		// Nonce protection.
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ?? '' ), 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

		$title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';

		try {
			$args = [
				'title' => $title,
			];

			// Create new spreadsheet.
			$spreadsheet = SpreadSheets::create( $args );

			// Save spreadsheet information.
			GsheetSettings::set_spreadsheets_id( $spreadsheet->spreadsheetId ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			GsheetSettings::set_spreadsheets_url( $spreadsheet->spreadsheetUrl ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		} catch ( Exception $e ) {
			wp_send_json_error(
				[
					'type'    => 'error',
					'message' => $e->getMessage(),
				]
			);
			wp_die();
		}

		wp_send_json_success();
		wp_die();
	}

	/**
	 * Ajax functionality for set spreadsheet cells label.
	 *
	 * @return void
	 */
	public function set_spreadsheets_label_process() {
		// Nonce protection.
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ?? '' ), 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

		$spreadsheet_id = isset( $_POST['spreadsheet_id'] ) ? sanitize_text_field( $_POST['spreadsheet_id'] ) : '';

		try {
			// Set spreadsheet cells label.
			SpreadSheets::set_sheet_label( $spreadsheet_id );
		} catch ( Exception $e ) {
			wp_send_json_error(
				[
					'type'    => 'error',
					'message' => $e->getMessage(),
				]
			);
			wp_die();
		}

		GsheetSettings::set_is_set_sheet_label( true );

		wp_send_json_success();
		wp_die();
	}

	/**
	 * Ajax functionality for reset spreadsheet cells label.
	 *
	 * @return void
	 */
	public function gsheet_reset_label_process() {
		// Nonce protection.
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ?? '' ), 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

		GsheetSettings::set_is_set_sheet_label( false );

		wp_send_json_success();
		wp_die();
	}

	/**
	 * Ajax functionality to unlink a spreadsheet
	 *
	 * @return void
	 */
	public function unlink_google_spreadsheets_process() {
		// Nonce protection.
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ?? '' ), 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

		$spreadsheet_id    = isset( $_POST['spreadsheet_id'] ) ? sanitize_text_field( $_POST['spreadsheet_id'] ) : '';
		$delete_from_drive = isset( $_POST['delete_spreadsheet_from_drive'] ) ? sanitize_text_field( $_POST['delete_spreadsheet_from_drive'] ) : '';

		try {
			wpadgsoauto_reset_gsheet_config();

			// Delete SpreadSheet From Drive on user preference.
			if ( true === $delete_from_drive ) {
				SpreadSheets::delete( $spreadsheet_id );
			}
		} catch ( Exception $e ) {
			wp_send_json_error(
				[
					'type'    => 'error',
					'message' => $e->getMessage(),
				]
			);
			wp_die();
		}

		wp_send_json_success();
		wp_die();
	}

	/**
	 * Ajax functionality to disconnect google oauth.
	 *
	 * @return void
	 */
	public function gsheet_disconnect_auth_process() {
		// Nonce protection.
		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ?? '' ), 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

		try {
			GsheetSettings::set_access_token( [] );
		} catch ( Exception $e ) {
			wp_send_json_error(
				[
					'type'    => 'error',
					'message' => $e->getMessage(),
				]
			);
			wp_die();
		}

		wp_send_json_success();
		wp_die();
	}
}
