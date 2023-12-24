<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
	</th>
	<td class="forminp">
		<div id="gsheets-repeater" class="repeater-field">
			<div id="gsheets-repeater-fields" class="fields">
			<?php if ( ! empty( $columns ) ) { ?>
				<?php foreach ( $columns as $index => $item ) { ?>
				<div class="field-group">
					<select name="<?php echo esc_attr( $value['id'] ); ?>[<?php echo esc_attr( $index ); ?>][item_data]" id="">
						<?php foreach ( $gsheet_columns as $key => $label ) { ?>
							<?php $selected = ( $key === $item['item_data'] ) ? 'selected' : ''; ?>
							<?php echo sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), esc_attr( $selected ), esc_attr( $label ) ); ?>
						<?php } ?>
					</select>
					<input type="text" name="<?php echo esc_attr( $value['id'] ); ?>[<?php echo esc_attr( $index ); ?>][label]" id="" placeholder="SpreadSheet Column Label" value="<?php echo esc_attr( $item['label'] ); ?>">
					<button type="button" class="my-field-remove button">X</button>
				</div>
				<?php } ?>
			<?php } ?>
			</div>
			<?php if ( ! $is_set_label ) { ?>
				<p><button type="button" class="gsheets-repeater-add button button-primary button-large"><?php esc_html_e( 'Add Column', 'wpappsdev-gsheet-order-automation' ); ?></button></p>
			<?php } ?>
		</div>

		<!-- Template -->
		<script type="text/html" id="tmpl-gsheets-repeater-field-group">
			<div class="field-group">

				<select name="<?php echo esc_attr( $value['id'] ); ?>[{{{data.id}}}][item_data]" id="">
					<?php foreach ( $gsheet_columns as $key => $label ) { ?>
						<?php echo sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $key ), esc_attr( $label ) ); ?>
					<?php } ?>
				</select>
				<input type="text" name="<?php echo esc_attr( $value['id'] ); ?>[{{{data.id}}}][label]" id="" placeholder="Column Label">
				<button type="button" class="my-field-remove button">X</button>
			</div>
		</script>
		<!-- End Template -->
	</td>
</tr>
