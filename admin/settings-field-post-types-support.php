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

					$groups = [
						'public'     => [
							'label'   => __( 'Public', 'pronamic-post-expiration' ),
							'options' => [],
						],
						'non-public' => [
							'label'   => __( 'Non Public', 'pronamic-post-expiration' ),
							'options' => [],
						],
					];

					foreach ( $post_status_objects as $key => $post_status_info ) {
						$group = $post_status_info->public ? 'public' : 'non-public';

						$groups[ $group ]['options'][ $key ] = \sprintf(
							/* translators: 1: Post status label, 2: Post status name */
							\__( '%1$s (%2$s)', 'pronamic-post-expiration' ),
							\esc_html( $post_status_info->label ),
							\esc_html( $post_status_info->name )
						);
					}

					$name = \sprintf(
						'pronamic_post_expiration_config[post_types][%s][post_status]',
						$post_type_info->name
					);

					?>
					<select name="<?php echo \esc_attr( $name ); ?>" <?php \disabled( $disabled ); ?>>
						<option value=""></option>

						<?php

						foreach ( $groups as $group ) {
							if ( 0 === count( $group['options'] ) ) {
								continue;
							}

							?>

							<optgroup label="<?php echo \esc_attr( $group['label'] ); ?>">

								<?php

								foreach ( $group['options'] as $value => $label ) {
									printf(
										'<option value="%s" %s>%s</option>',
										\esc_attr( $value ),
										\selected( $value, $current, false ),
										\esc_html( $label )
									);
								}

								?>

							</optgroup>

							<?php
						}

						?>
					</select>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
