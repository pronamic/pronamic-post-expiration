<?php
/**
 * Settings field post types
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPressCloudflare
 */

namespace Pronamic\PostExpiration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_type_objects = \get_post_types(
	[
		'public' => true,
	],
	'objects'
);

$post_status_objects = \get_post_stati(
	[
		'internal' => false,
	],
	'objects'
);

?>
<style type="text/css">
	.form-table .widefat th,
	.form-table .widefat td {
		padding: 8px 10px;
	}
</style>

<table class="widefat">
	<thead>
		<tr>
			<th><?php \esc_html_e( 'Post type', 'pronamic-post-expiration' ); ?></th>
			<th><?php \esc_html_e( 'Label', 'pronamic-post-expiration' ); ?></th>
			<th><?php \esc_html_e( 'Support', 'pronamic-post-expiration' ); ?></th>
			<th><?php \esc_html_e( 'Post status', 'pronamic-post-expiration' ); ?></th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ( $post_type_objects as $post_type_info ) : ?>

			<tr>
				<td>
					<code><?php echo \esc_html( $post_type_info->name ); ?></code>
				</td>
				<td>
					<?php echo \esc_html( $post_type_info->label ); ?>
				</td>
				<td>
					<?php

					$post_expiration_info = PostExpirationInfo::get_from_post_type( $post_type_info->name );

					$checked  = false;
					$disabled = false;

					if ( null !== $post_expiration_info ) {
						$checked  = true;
						$disabled = ( 'option' !== $post_expiration_info->source );
					}

					?>
					<input type="checkbox" name="pronamic_post_expiration_post_types[]" value="<?php echo \esc_attr( $post_type_info->name ); ?>" <?php \checked( $checked ); ?> <?php \disabled( $disabled ); ?> />
				</td>
				<td>
					<?php

					if ( null !== $post_expiration_info ) {
						\printf(
							'<code>%s</code>',
							\esc_html( $post_expiration_info->post_status )
						);
					}

					$options = [
						'' => '',
					];

					foreach ( $post_status_objects as $key => $post_status_info ) {
						$options[ $key ] = $post_status_info->label;
					}

					?>
					<select>
						<?php

						foreach ( $options as $value => $label ) {
							printf(
								'<option value="%s" %s>%s</option>',
								\esc_attr( $value ),
								\checked( $value, '', false ),
								\esc_html( $label )
							);
						}

						?>
					</select>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
