<?php

namespace WPAppsDev\GSOA;

use WPAppsDev\GSOA\Api\SpreadSheets;
use WPAppsDev\GSOA\Supports\GsheetSettings;

/**
 * The google sheet processing class.
 */
class WcGoogleSheet {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'woocommerce_update_order', [ $this, 'update_google_sheets_data' ], 10, 2 );
	}

	/**
	 * Update order in google sheet.
	 *
	 * @param int    $order_id Order id.
	 * @param object $order Order object.
	 *
	 * @return void
	 */
	public function update_google_sheets_data( $order_id, $order ) {
		$sheets_updated = (int) $order->get_meta( '_google_sheets_updated' );

		if ( 1 === $sheets_updated ) {
			return;
		}

		$order_data = [];
		$columns    = GsheetSettings::get_sheets_columns_data();
		$row_items  = wp_list_pluck( $columns, 'item_data' );

		foreach ( $row_items as $item ) {
			$item_value = '';

			switch ( $item ) {
				case 'order_date':
					$item_value = $order->get_date_created()->date( 'm/d/Y' );

					break;

				case 'order_number':
					$item_value = $order->get_id();

					break;

				case 'customer_name':
					$item_value = $order->get_formatted_billing_full_name();

					break;

				case 'products':
					$products_name = [];

					foreach ( $order->get_items() as $item_id => $item ) {
						$products_name[] = $item->get_name();
					}

					$item_value = implode( ' + ', $products_name );

					break;

				case 'order_total':
					$item_value = $order->get_total();

					break;

				case 'address':
					$address1 = $order->get_billing_address_1();
					$address2 = $order->get_billing_address_2();
					$city     = $order->get_billing_city();
					$postcode = $order->get_billing_postcode();
					$address  = "{$address1} {$address2} {$city}, {$postcode}";

					$item_value = $address;

					break;

				default:
					$item_value = apply_filters( 'wpadgsoauto_gsheet_order_data_item_value', $item_value, $item, $order );

					break;
			}

			if ( isset( $item_value ) && ! empty( $item_value ) ) {
				$order_data[] = $item_value;
			}
		}

		$rows           = [ $order_data ];
		$spreadsheet_id = GsheetSettings::get_spreadsheets_id();
		$response       = SpreadSheets::add_order_row_data( $spreadsheet_id, $rows );

		if ( $response ) {
			$get_updates   = $response->getUpdates();
			$updated_range = $get_updates->getUpdatedRange();
			$order->update_meta_data( 'google_sheets_updated_range', $updated_range );
			$order->update_meta_data( '_google_sheets_updated', 1 );
			$order->save();
			Logger::log( sprintf( 'Spreadsheet append response. SpreadsheetId: %1$s. Response: ', $spreadsheet_id ) . print_r( $response, true ) ); // phpcs:ignore
		}
	}
}
