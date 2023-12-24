<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
	</th>
	<td class="forminp">
		<a class="components-button is-primary <?php echo esc_attr( ( isset( $value['class'] ) ? $value['class'] : '' ) ); ?>"
			href="<?php echo esc_url( $value['url'] ); ?>"
			target="_TOP"><?php echo esc_attr( $value['button-text'] ); ?> </a>
		<p><?php echo wp_kses( $value['des'], wpadgsoauto_allowed_html() ); ?></p>
	</td>
</tr>
