<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for='<?php echo esc_attr( $value['id'] ); ?>'><?php echo esc_html( $value['title'] ); ?></label>
	</th>
	<td class="forminp">
		<div class="sheets-actions-button">
			<?php if ( ! $is_set_label ) { ?>
				<button type="button" class="gsheets-set-label button button-primary button-large"><?php esc_html_e( 'Set SpreadSheet Label', 'wpappsdev-gsheet-order-automation' ); ?></button>
			<?php } ?>
			<?php if ( $is_set_label ) { ?>
				<button type="button" class="gsheets-reset-label button button-primary button-large"><?php esc_html_e( 'Reset SpreadSheet Label', 'wpappsdev-gsheet-order-automation' ); ?></button>
			<?php } ?>
			<?php do_action( 'wpadgsoauto_google_sheet_actions' ); ?>
		</div>
	</td>
</tr>
