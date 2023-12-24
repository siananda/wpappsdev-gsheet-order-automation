<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="updated" id="installer-notice" style="padding: 1em; position: relative;">
	<h2><?php esc_html_e( 'Required plugin notice for WPAppsDev - GSheet Order Automation', 'wpappsdev-gsheet-order-automation' ); ?></h2>
	<?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( 'woocommerce/woocommerce.php' ) ) { ?>
		<p>
			<?php
				echo sprintf(
					/* translators: %s - WooCommerce Plugin Name */
					esc_html__( 'You just need to activate the %s to make it functional.', 'wpappsdev-gsheet-order-automation' ),
					'<strong>WooCommerce</strong>'
				);
			?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ) ); ?>"  title="<?php esc_html_e( 'Activate this plugin', 'wpappsdev-gsheet-order-automation' ); ?>"><?php esc_html_e( 'Activate', 'wpappsdev-gsheet-order-automation' ); ?></a>
		</p>
	<?php } else { ?>
		<p><?php esc_html_e( 'You just need to install & active the WooCommerce to make it functional.', 'wpappsdev-gsheet-order-automation' ); ?></p>
	<?php } ?>
</div>
