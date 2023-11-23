<?php

namespace WPAppsDev\GSOA\Admin;

use WPAppsDev\GSOA\Api\Auth;
use WPAppsDev\GSOA\Supports\GsheetSettings;

/**
 * WooCommerce settings class.
 */
class WcSettings {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		// Custom settings page for woocommerce.
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_wpadgsoauto-sheets', __CLASS__ . '::sheets_settings_tab' );
		add_action( 'woocommerce_update_options_wpadgsoauto-sheets', __CLASS__ . '::update_sheets_settings' );
	}

	/**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @param array $settings_tabs array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
	 *
	 * @return array $settings_tabs array of WooCommerce setting tabs & their labels, including the Subscription tab
	 */
	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs['wpadgsoauto-sheets'] = __( 'Google Sheets', 'wpappsdev-gsheet-order-automation' );

		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses self::get_invoice_settings()
	 */
	public static function sheets_settings_tab() {
		// @codingStandardsIgnoreStart
		if ( isset( $_GET['code'] ) ) {
			Auth::get_oauth_token( sanitize_text_field( $_GET['code'] ) );
		}
		// @codingStandardsIgnoreEnd

		woocommerce_admin_fields( self::get_sheets_settings() );
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses self::get_invoice_settings()
	 */
	public static function update_sheets_settings() {
		woocommerce_update_options( self::get_sheets_settings() );
	}

	/**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @return array array of settings for @see woocommerce_admin_fields() function
	 */
	public static function get_sheets_settings() {
		$settings = [
			'section_title'                    => [
				'id'   => 'wc_gsheet_api_info_section_title',
				'name' => __( 'Google Sheets Integration Settings', 'wpappsdev-gsheet-order-automation' ),
				'type' => 'title',
				'desc' => sprintf(
					'%s <br/> %s ',
					sprintf(
						'%s <a href="%s" target="_blank">%s</a> %s',
						__( 'The following system configuration are required to use the plugin. Please check this ', 'mplus-dokan-stripe-connect' ),
						'https://developers.google.com/workspace/guides/create-project',
						__( 'documentation', 'mplus-dokan-stripe-connect' ),
						__( 'to understand how to setup a Google project for Client ID and Client secret.', 'mplus-dokan-stripe-connect' ),
					),
					sprintf(
						'%s <code>%s</code> %s <a href="%s" target="_blank">%s</a> %s',
						__( 'Set your authorize redirect uri', 'mplus-dokan-stripe-connect' ),
						admin_url( 'admin.php?page=wc-settings&tab=wpadgsoauto-sheets' ),
						__( 'in your Google OAuth 2.0 ', 'mplus-dokan-stripe-connect' ),
						'https://console.cloud.google.com/apis/credentials',
						__( 'Credentials settings', 'mplus-dokan-stripe-connect' ),
						__( 'for redirects', 'mplus-dokan-stripe-connect' ),
					),
				),
			],
			'wpadgsoauto_google_client_id'     => [
				'name'              => __( 'Client ID', 'wpappsdev-gsheet-order-automation' ),
				'type'              => 'text',
				'id'                => 'wpadgsoauto_google_client_id',
				'placeholder'       => __( 'Client ID', 'wpappsdev-gsheet-order-automation' ),
				'custom_attributes' => [
					'autocomplete' => 'off',
				],
			],
			'wpadgsoauto_google_client_secret' => [
				'name'              => __( 'Client Secret', 'wpappsdev-gsheet-order-automation' ),
				'type'              => 'text',
				'id'                => 'wpadgsoauto_google_client_secret',
				'placeholder'       => __( 'Client Secret', 'wpappsdev-gsheet-order-automation' ),
				'custom_attributes' => [
					'autocomplete' => 'off',
				],
			],
		];

		if ( Auth::is_ready_for_oauth() ) {
			$oauth_button = [
				'name'        => __( 'Google Oauth', 'wpappsdev-gsheet-order-automation' ),
				'type'        => 'button',
				'id'          => 'wpadgsoauto_google_oauth',
				'des'         => __( 'Your account is not connected with Google Account. Connect your Google account to synchronize google sheet.', 'wpappsdev-gsheet-order-automation' ),
				'disabled'    => false,
				'button-text' => __( 'Connect Google Oauth', 'wpappsdev-gsheet-order-automation' ),
				'url'         => '#',
			];

			$is_token_expired = Auth::is_token_expired();

			if ( $is_token_expired ) {
				$auth_url            = Auth::auth_url();
				$oauth_button['url'] = $auth_url;
			} else {
				$oauth_button['button-text'] = __( 'Disconnect', 'wpappsdev-gsheet-order-automation' );
				$oauth_button['des']         = __( 'Your account is connected with Google Account.', 'wpappsdev-gsheet-order-automation' );
				$oauth_button['disabled']    = false;
				$oauth_button['class']       = 'wpadgsoauto_disconnect_google_connect wpadgsoauto-red';
			}

			$settings['wpadgsoauto_google_oauth'] = $oauth_button;

			if ( ! $is_token_expired ) {
				$settings['wpadgsoauto_spreadsheets_title'] = [
					'name'        => __( 'SpreadSheets Title', 'wpappsdev-gsheet-order-automation' ),
					'type'        => 'text',
					'id'          => 'wpadgsoauto_spreadsheets_title',
					'placeholder' => __( 'SpreadSheets Title', 'wpappsdev-gsheet-order-automation' ),
				];

				$spreadsheets_button = [
					'name'        => __( 'Spreadsheets', 'wpappsdev-gsheet-order-automation' ),
					'type'        => 'button',
					'id'          => 'wpadgsoauto_spreadsheets_button',
					'des'         => __( 'Create SpreadSheets and link spreadsheets with this system.', 'wpappsdev-gsheet-order-automation' ),
					'disabled'    => false,
					'button-text' => __( 'Create SpreadSheet', 'wpappsdev-gsheet-order-automation' ),
					'url'         => '#',
					'class'       => 'create-google-spreadsheets',
				];

				$spreadsheet_id = GsheetSettings::get_spreadsheets_id();

				if ( '' !== $spreadsheet_id ) {
					$link_url                           = GsheetSettings::get_spreadsheets_url();
					$link                               = sprintf( '<a target="_blank" href="%1$s">%2$s</a>', $link_url, __( 'SpreadSheet Link', 'wpappsdev-gsheet-order-automation' ) );
					$spreadsheets_button['button-text'] = __( 'Unlink & Removed', 'wpappsdev-gsheet-order-automation' );
					$spreadsheets_button['class']       = 'unlink-google-spreadsheets';
					$spreadsheets_button['des']         = __( 'Unlink spreadsheet with this system.', 'wpappsdev-gsheet-order-automation' ) . " {$link}";
				}

				$settings['wpadgsoauto_spreadsheets_button'] = $spreadsheets_button;

				if ( '' !== $spreadsheet_id ) {
					$settings['wpadgsoauto_google_spreadsheets_id'] = [
						'name'              => __( 'Google Sheet ID', 'wpappsdev-gsheet-order-automation' ),
						'type'              => 'text',
						'id'                => 'wpadgsoauto_google_spreadsheets_id',
						'placeholder'       => __( 'Google Sheet ID', 'wpappsdev-gsheet-order-automation' ),
						'custom_attributes' => [ 'disabled' => 'disabled' ],
					];

					$settings['wpadgsoauto_sheets_columns_data'] = [
						'name' => __( 'SpreadSheet Column Data', 'wpappsdev-gsheet-order-automation' ),
						'type' => 'repeatable',
						'id'   => 'wpadgsoauto_sheets_columns_data',
					];

					$settings['wpadgsoauto_spreadsheets_bg_color'] = [
						'name' => __( 'Label BackGround Color', 'wpappsdev-gsheet-order-automation' ),
						'type' => 'color',
						'id'   => 'wpadgsoauto_spreadsheets_bg_color',
					];

					$settings['wpadgsoauto_spreadsheets_fg_color'] = [
						'name' => __( 'Label Text Color', 'wpappsdev-gsheet-order-automation' ),
						'type' => 'color',
						'id'   => 'wpadgsoauto_spreadsheets_fg_color',
					];

					$columns = GsheetSettings::get_sheets_columns_data();

					if ( ! empty( $columns ) ) {
						$settings['wpadgsoauto_spreadsheets_sheet_actions'] = [
							'name' => '',
							'type' => 'sheetlbaction',
							'id'   => 'wpadgsoauto_spreadsheets_sheet_actions',
						];
					}
				}
			}
		}
		$settings['section_end'] = [
			'type' => 'sectionend',
			'id'   => 'wc_gsheet_api_info_section_end',
		];

		return apply_filters( 'wpadgsoauto_google_sheets_settings', $settings );
	}
}
