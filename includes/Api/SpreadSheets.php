<?php

namespace  WPAppsDev\GSOA\Api;

use Exception;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\CellFormat;
use Google\Service\Sheets\Color;
use Google\Service\Sheets\DimensionProperties;
use Google\Service\Sheets\DimensionRange;
use Google\Service\Sheets\GridRange;
use Google\Service\Sheets\Padding;
use Google\Service\Sheets\RepeatCellRequest;
use Google\Service\Sheets\Request;
use Google\Service\Sheets\SheetProperties;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\TextFormat;
use Google\Service\Sheets\UpdateDimensionPropertiesRequest;
use Google\Service\Sheets\UpdateSheetPropertiesRequest;
use Google\Service\Sheets\ValueRange;
use WPAppsDev\GSOA\ErrorException;
use WPAppsDev\GSOA\Logger;
use WPAppsDev\GSOA\Supports\GsheetApi as Api;
use WPAppsDev\GSOA\Supports\GsheetSettings;

/**
 * The spreadsheets class.
 */
class SpreadSheets extends Api {
	/**
	 * Creates a spreadsheets.
	 *
	 * @param array $args Arguments.
	 *
	 * @return mixed
	 *
	 * @throws ErrorException Error Exception.
	 */
	public static function create( $args = [] ) {
		if ( array_key_exists( 'title', $args ) && ! empty( $args['title'] ) ) {
			$title = $args['title'];
		} else {
			$title = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		}

		try {
			$service = self::spreadsheets();
			$args    = [
				'properties' => [
					'title' => $title,
				],
			];

			$spreadsheet = new Spreadsheet( $args );
			$spreadsheet = $service->spreadsheets->create( $spreadsheet );

			// Update first working sheet title when create a new spreadsheet.
			self::update_sheet_title( $service, $spreadsheet );

			return $spreadsheet;
		} catch ( Exception $e ) {
			Logger::log( sprintf( 'Could not create spreadsheet. Error: %s', $e->getMessage() ) );

			/* translators: error message */
			throw new ErrorException( 'mplus-stripe-intent-error', sprintf( __( 'Could not create spreadsheet. Error: %s', 'mplus-dokan-stripe-connect' ), $e->getMessage() ) );
		}
	}

	/**
	 * Retrieves a spreadsheets.
	 *
	 * @param string $spreadsheet_id Spreadsheet id.
	 *
	 * @return object|false
	 */
	public static function get( $spreadsheet_id ) {
		try {
			$spreadsheets = self::spreadsheets();

			return $spreadsheets->spreadsheets->get( $spreadsheet_id );
		} catch ( Exception $e ) {
			Logger::log( sprintf( 'Could not retrieve spreadsheet for id: %1$s. Error: %2$s', $spreadsheet_id, $e->getMessage() ) );

			return false;
		}
	}

	/**
	 * Delete spreadsheets.
	 *
	 * @param string $spreadsheet_id Spreadsheet id.
	 * @param array  $options Extra arguments.
	 *
	 * @return object|false
	 */
	public static function delete( $spreadsheet_id, $options = [] ) {
		return Drives::delete( $spreadsheet_id, $options );
	}

	/**
	 * Google sheets batch update request.
	 *
	 * @param string $spreadsheet_id Spreadsheet id.
	 * @param object $requests  Requested data.
	 *
	 * @return mixed
	 */
	private static function batchupdate( $spreadsheet_id, $requests ) {
		$batch_request = new BatchUpdateSpreadsheetRequest();
		$batch_request->setIncludeSpreadsheetInResponse( true );
		$batch_request->setRequests( $requests );

		try {
			return self::spreadsheets()->spreadsheets->batchUpdate( $spreadsheet_id, $batch_request );
		} catch ( Exception $e ) {
			Logger::log( sprintf( 'Could not process batch update request. SpreadsheetId: %1$s. Error: %2$s', $spreadsheet_id, $e->getMessage() ) );
			Logger::log( sprintf( 'Requests data. SpreadsheetId: %1$s. Error: ', $spreadsheet_id ) . print_r( $requests ) ); // phpcs:ignore

			return false;
		}
	}

	/**
	 * Spreadsheets values update.
	 *
	 * @param string $spreadsheet_id Spreadsheet id.
	 * @param string $range Sheet range.
	 * @param object $update_body Updated body data.
	 * @param array  $params Extra parameters.
	 *
	 * @return mixed
	 */
	private static function valuesupdate( $spreadsheet_id, $range, $update_body, $params ) {
		try {
			return self::spreadsheets()->spreadsheets_values->update( $spreadsheet_id, $range, $update_body, $params );
		} catch ( Exception $e ) {
			Logger::log( sprintf( 'Could not process spreadsheets values update request. SpreadsheetId: %1$s. Error: %2$s', $spreadsheet_id, $e->getMessage() ) );
			Logger::log( sprintf( 'Requests data. SpreadsheetId: %1$s. Error: ', $spreadsheet_id ) . print_r( $update_body ) ); // phpcs:ignore

			return false;
		}
	}

	/**
	 * Spreadsheets values update.
	 *
	 * @param string $spreadsheet_id Spreadsheet id.
	 * @param string $range Sheet range.
	 * @param object $request_body Requested data.
	 * @param array  $params Extra parameters.
	 *
	 * @return mixed
	 */
	private static function valuesappend( $spreadsheet_id, $range, $request_body, $params ) {
		try {
			return self::spreadsheets()->spreadsheets_values->append( $spreadsheet_id, $range, $request_body, $params );
		} catch ( Exception $e ) {
			Logger::log( sprintf( 'Could not process spreadsheets values append request. SpreadsheetId: %1$s. Error: %2$s', $spreadsheet_id, $e->getMessage() ) );
			Logger::log( sprintf( 'Requests data. SpreadsheetId: %1$s. Error: ', $spreadsheet_id ) . print_r( $update_body ) ); // phpcs:ignore

			return false;
		}
	}

	/**
	 * Initialize google sheets service object.
	 *
	 * @return object
	 */
	private static function spreadsheets() {
		$client = self::api();

		return new Sheets( $client );
	}

	/**
	 * Add new sheet for a spreadsheet.
	 *
	 * @param string $spreadsheet_id Spreadsheet id.
	 * @param array  $sheet_data Spreadsheet data.
	 *
	 * @return void
	 */
	public static function add_new_sheet( $spreadsheet_id, $sheet_data ) {
	}

	/**
	 * Update google spreadsheet work sheet title.
	 *
	 * @param object    $service Google API service object.
	 * @param object    $spreadsheet Spreadsheet object.
	 * @param string    $title Sheet title.
	 * @param init|null $sheet_id Sheet id.
	 *
	 * @return void
	 *
	 * @throws ErrorException Error Exception.
	 */
	public static function update_sheet_title( $service, $spreadsheet, $title = '', $sheet_id = null ) {
		if ( is_null( $sheet_id ) ) {
			// rename first sheet.
			if ( ! $spreadsheet->getSheets() ) {
				throw new ErrorException( 'wpadgsoauto-gsheet-error', 'No work sheet available.' );
			}
			$sheet_id = $spreadsheet->getSheets()[0]->getProperties()->getSheetId();
		}

		if ( '' === $title ) {
			$title = wpadgsoauto_get_sheet_title();
		}

		$title_prop = new SheetProperties();
		$title_prop->setSheetId( $sheet_id );
		$title_prop->setTitle( $title );

		$rename_req = new UpdateSheetPropertiesRequest();
		$rename_req->setProperties( $title_prop );
		$rename_req->setFields( 'title' );

		$request = new Request();
		$request->setUpdateSheetProperties( $rename_req );

		$title_update = self::batchupdate( $spreadsheet->spreadsheetId, $request ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( ! $title_update ) {
			Logger::log( sprintf( 'Could not update work sheet title for spreadsheet id: %1$s.', $spreadsheet_id ) );
		}
	}

	/**
	 * Updates the labels of the specified Google Sheet
	 *
	 * @param  string $spreadsheet_id Spreadsheet id.
	 * @return bool
	 */
	public static function set_sheet_label( $spreadsheet_id ) {
		$spreadsheet = self::get( $spreadsheet_id );

		if ( $spreadsheet ) {
			$latest     = end( $spreadsheet->sheets );
			$properties = $latest->getProperties();
			$title      = $properties->title;
			$sheet_id   = $properties->sheetId; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$range      = "{$title}!A1";
			$columns    = GsheetSettings::get_sheets_columns_data();
			$labels     = wp_list_pluck( $columns, 'label' );
			$values     = [ $labels ];

			$value_range_args = [
				'range'          => $range,
				'majorDimension' => 'ROWS',
				'values'         => $values,
			];

			$update_body = new ValueRange( $value_range_args );

			$params = [
				'valueInputOption' => 'USER_ENTERED',
			];

			self::set_sheet_label_formatting( $spreadsheet_id, $sheet_id );

			return self::valuesupdate( $spreadsheet_id, $range, $update_body, $params );
		}

		return false;
	}

	/**
	 * Format sheet label color and size.
	 *
	 * @param  string $spreadsheet_id Spreadsheet id.
	 * @param  string $sheet_id Spreadsheet sheet id.
	 * @return void
	 */
	public static function set_sheet_label_formatting( $spreadsheet_id, $sheet_id ) {
		// For range, end rows and end columns are not considered for updating. Index start from 0.
		$range = new GridRange();
		$range->setSheetId( $sheet_id );
		$range->setStartRowIndex( 0 );
		$range->setEndRowIndex( 1 );
		$range->setStartColumnIndex( 0 );

		// set label background color value in RGBA.
		$bg_color_value = wpadgsoauto_convert_hex_to_rgb( GsheetSettings::get_label_bg_color() );
		$bg_color       = new Color();

		if ( count( $bg_color_value ) === 3 ) {
			$bg_color->setRed( $bg_color_value[0] / 255 );
			$bg_color->setGreen( $bg_color_value[1] / 255 );
			$bg_color->setBlue( $bg_color_value[2] / 255 );
			$bg_color->setAlpha( 1 );
		} else {
			// Set label default background color black.
			$bg_color->setRed( 0 );
			$bg_color->setGreen( 0 );
			$bg_color->setBlue( 0 );
			$bg_color->setAlpha( 1 );
		}

		// Set label text color value in RGBA.
		$fg_color_value = wpadgsoauto_convert_hex_to_rgb( GsheetSettings::get_label_text_color() );
		$fg_color       = new Color();

		if ( count( $fg_color_value ) === 3 ) {
			$fg_color->setRed( $fg_color_value[0] / 255 );
			$fg_color->setGreen( $fg_color_value[1] / 255 );
			$fg_color->setBlue( $fg_color_value[2] / 255 );
			$fg_color->setAlpha( 1 );
		} else {
			// Set label text default color white.
			$fg_color->setRed( 1 );
			$fg_color->setGreen( 1 );
			$fg_color->setBlue( 1 );
			$fg_color->setAlpha( 1 );
		}

		// text_format is used to set different text formats like Bold, font size.
		$text_format = new TextFormat();
		$text_format->setBold( true );
		$text_format->setFontSize( 14 );
		$text_format->setForegroundColor( $fg_color );

		$cell_padding = new Padding();
		$cell_padding->setTop( 3 );
		$cell_padding->setBottom( 3 );
		$cell_padding->setLeft( 2 );
		$cell_padding->setRight( 2 );

		// cell_format is used to set different properties of a cell.
		$cell_format = new CellFormat();
		$cell_format->setBackgroundColor( $bg_color );
		$cell_format->setTextFormat( $text_format );
		$cell_format->setHorizontalAlignment( 'CENTER' );
		$cell_format->setPadding( $cell_padding );

		// New cell class. Assign the cell_format to it.
		$cell = new CellData();
		$cell->setUserEnteredFormat( $cell_format );

		// repeat_cell request is used to assign requests to range of cells
		// Fields is used to specify which properties of a cell to update
		// Here we are updating two properties. So both are specified and separated by comma.
		$repeat_cell = new RepeatCellRequest();
		$repeat_cell->setRange( $range );
		$repeat_cell->setCell( $cell );
		$repeat_cell->setFields( '*' );

		// Set repeat_cellRequest.
		$requests = new Request();
		$requests->setRepeatCell( $repeat_cell );
		self::batchupdate( $spreadsheet_id, $requests );

		$range = new DimensionRange();
		$range->setSheetId( $sheet_id );
		$range->setStartIndex( 0 );
		$range->setDimension( 'COLUMNS' );

		$dimension_properties = new DimensionProperties();
		$dimension_properties->setPixelSize( 200 );

		$update_dimension_properties = new UpdateDimensionPropertiesRequest();
		$update_dimension_properties->setRange( $range );
		$update_dimension_properties->setProperties( $dimension_properties );
		$update_dimension_properties->setFields( 'pixelSize' );

		$requests = new Request();
		$requests->setUpdateDimensionProperties( $update_dimension_properties );

		self::batchupdate( $spreadsheet_id, $requests );
	}

	/**
	 * Add spreadsheet order row data.
	 *
	 * @param string $spreadsheet_id Spreadsheet id.
	 * @param array  $data Spreadsheet values.
	 *
	 * @return mixed
	 */
	public static function add_order_row_data( $spreadsheet_id, $data ) {
		$range  = wpadgsoauto_get_sheet_title();
		$params = [
			'valueInputOption' => 'USER_ENTERED',
		];

		$value_range_args = [
			'majorDimension' => 'ROWS',
			'values'         => $data,
		];

		$request_body = new ValueRange( $value_range_args );

		return self::valuesappend( $spreadsheet_id, $range, $request_body, $params );
	}
}
