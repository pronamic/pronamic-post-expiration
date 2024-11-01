<?php
/**
 * Settings field post types
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPressCloudflare
 */

namespace Pronamic\WordPressPostExpiration;

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

$post_status_objects = \array_filter(
	$post_status_objects,
	function ( $post_status_object ) {
		return ! \in_array( $post_status_object->name, [ 'future', 'pending' ], true );
	}
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
				<?php

				$post_expiration_info = PostExpirationInfo::get_from_post_type( $post_type_info->name );

				$checked  = false;
				$disabled = false;

				if ( null !== $post_expiration_info ) {
					$checked  = true;
					$disabled = ( 'option' !== $post_expiration_info->source );
				}

				?>
				<td>
					<code><?php echo \esc_html( $post_type_info->name ); ?></code>
				</td>
				<td>
					<?php echo \esc_html( $post_type_info->label ); ?>
				</td>
				<td>
					<?php

					$name = \sprintf(
						'pronamic_post_expiration_config[post_types][%s][support]',
						$post_type_info->name
					);

					?>
					<input type="checkbox" name="<?php echo \esc_attr( $name ); ?>" value="1" <?php \checked( $checked ); ?> <?php \disabled( $disabled ); ?> />
				</td>
				<td>
					<?php

					$current = ( null === $post_expiration_info ) ? '' : $post_expiration_info->post_status;

					$options = [
						'' => '',
					];

					foreach ( $post_status_objects as $key => $post_status_info ) {
						$options[ $key ] = \sprintf(
							/* translators: 1: Post status label, 2: Post status name, 3: Public yes/no. */
							\__( '%1$s (%2$s) · Public: %3$s', 'pronamic-post-expiration' ),
							\esc_html( $post_status_info->label ),
							\esc_html( $post_status_info->name ),
							\esc_html( $post_status_info->public ? \__( 'Yes', 'pronamic-post-expiration' ) : \__( 'No', 'pronamic-post-expiration' ) )
						);
					}

					if ( ! \array_key_exists( $current, $options ) ) {
						$options[ $current ] = $current;
					}

					$name = \sprintf(
						'pronamic_post_expiration_config[post_types][%s][post_status]',
						$post_type_info->name
					);

					?>
					<select name="<?php echo \esc_attr( $name ); ?>" <?php \disabled( $disabled ); ?>>
						<?php

						foreach ( $options as $value => $label ) {
							printf(
								'<option value="%s" %s>%s</option>',
								\esc_attr( $value ),
								\selected( $value, $current, false ),
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
