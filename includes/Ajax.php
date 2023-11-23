<?php

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
		$post_data = wc_clean( $_POST );
		$nonce     = isset( $post_data['_nonce'] ) ? wc_clean( $post_data['_nonce'] ) : '';
		$title     = isset( $post_data['title'] ) ? wc_clean( $post_data['title'] ) : '';

		// Nonce protection.
		if ( ! wp_verify_nonce( $nonce, 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

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
		$post_data      = wc_clean( $_POST );
		$nonce          = isset( $post_data['_nonce'] ) ? wc_clean( $post_data['_nonce'] ) : '';
		$spreadsheet_id = isset( $post_data['spreadsheet_id'] ) ? wc_clean( $post_data['spreadsheet_id'] ) : '';

		// Nonce protection.
		if ( ! wp_verify_nonce( $nonce, 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

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
		$post_data = wc_clean( $_POST );
		$nonce     = isset( $post_data['_nonce'] ) ? wc_clean( $post_data['_nonce'] ) : '';

		// Nonce protection.
		if ( ! wp_verify_nonce( $nonce, 'wpadgsoauto-admin-security' ) ) {
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
		$post_data         = wc_clean( $_POST );
		$nonce             = isset( $post_data['_nonce'] ) ? wc_clean( $post_data['_nonce'] ) : '';
		$spreadsheet_id    = isset( $post_data['spreadsheet_id'] ) ? wc_clean( $post_data['spreadsheet_id'] ) : '';
		$delete_from_drive = isset( $post_data['delete_spreadsheet_from_drive'] ) ? wc_clean( $post_data['delete_spreadsheet_from_drive'] ) : '';

		// Nonce protection.
		if ( ! wp_verify_nonce( $nonce, 'wpadgsoauto-admin-security' ) ) {
			wp_send_json_error(
				[
					'type'    => 'nonce',
					'message' => __( 'Are you cheating?', 'wpappsdev-gsheet-order-automation' ),
				]
			);

			wp_die();
		}

		try {
			reset_gsheet_configuration();

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
		$post_data = wc_clean( $_POST );
		$nonce     = isset( $post_data['_nonce'] ) ? wc_clean( $post_data['_nonce'] ) : '';

		// Nonce protection.
		if ( ! wp_verify_nonce( $nonce, 'wpadgsoauto-admin-security' ) ) {
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
