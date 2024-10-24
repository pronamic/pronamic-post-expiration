<?php
/**
 * Settings field post types
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPressCloudflare
 */

$post_type_objects = get_post_types(
	[
		'public' => true,
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
			<th><?php esc_html_e( 'Post type', 'pronamic-post-expiration' ); ?></th>
			<th><?php esc_html_e( 'Label', 'pronamic-post-expiration' ); ?></th>
			<th><?php esc_html_e( 'Support', 'pronamic-post-expiration' ); ?></th>
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

					$supports = \get_all_post_type_supports( $post_type_info->name );

					$checked  = false;
					$disabled = false;

					if ( post_type_supports( $post_type_info->name, 'expiration' ) ) {
						$checked  = true;
						$disabled = true;

						if ( isset( $supports['expiration']['source'] ) ) {
							$disabled = ( 'option' !== $supports['expiration']['source'] );
						}
					}

					?>
					<input type="checkbox" name="pronamic_post_expiration_post_types[]" value="<?php echo \esc_attr( $post_type_info->name ); ?>" <?php checked( $checked ); ?> <?php disabled( $disabled ); ?> />
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
