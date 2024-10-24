<?php
/**
 * Admin page settings
 *
 * @package Pronamic\WordPressCloudflare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'pronamic_post_expiration' ); ?>

		<?php do_settings_sections( 'pronamic_post_expiration' ); ?>

		<?php submit_button(); ?>
	</form>
</div>