<?php

namespace WPAppsDev\GSOA\Admin;

use WPAppsDev\GSOA\Supports\GsheetSettings;

/**
 * Undocumented class
 */
class WcSettingsFields {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		// Custom field for woocommerce settings field.
		add_action( 'woocommerce_admin_field_button', [ $this, 'button_field' ] );
		add_action( 'woocommerce_admin_field_repeatable', [ $this, 'repeatable_field' ] );
		add_action( 'woocommerce_admin_field_sheetlbaction', [ $this, 'sheet_label_action_fields' ] );
	}

	/**
	 * Custom button field for woocommerce settings field.
	 *
	 * @param array $value Field value.
	 *
	 * @return void
	 */
	public function button_field( $value ) {
		$args = [
			'value' => $value,
		];

		wpadgsoauto_get_template( 'admin/button.php', $args );
	}

	/**
	 * Custom repeatable field for woocommerce settings field.
	 *
	 * @param array $value Field value.
	 *
	 * @return void
	 */
	public function repeatable_field( $value ) {
		$args = [
			'value'          => $value,
			'gsheet_columns' => wpadgsoauto_gsheet_columns(),
			'columns'        => GsheetSettings::get_sheets_columns_data(),
			'is_set_label'   => GsheetSettings::is_set_sheet_label(),
		];

		wpadgsoauto_get_template( 'admin/repeatable.php', $args );
	}

	/**
	 * Custom color field for woocommerce settings field.
	 *
	 * @param array $value Field value.
	 *
	 * @return void
	 */
	public function sheet_label_action_fields( $value ) {
		$args = [
			'value'        => $value,
			'is_set_label' => GsheetSettings::is_set_sheet_label(),
		];

		wpadgsoauto_get_template( 'admin/sheet-label-actions.php', $args );
	}
}
