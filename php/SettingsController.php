<?php
/**
 * Settings controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\PostExpiration
 */

namespace Pronamic\PostExpiration;

/**
 * Settings controller class
 */
final class SettingsController {
	/**
	 * Setup.
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_action( 'admin_init', [ $this, 'admin_init' ] );

		\add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Initialize.
	 */
	public function init() {
		\register_setting(
			'pronamic_post_expiration',
			'pronamic_post_expiration_post_types'
		);
	}

	/**
	 * Admin initialize.
	 */
	public function admin_init() {
		\add_settings_section(
			'pronamic_post_expiration_general',
			\__( 'General', 'pronamic-post-expiration' ),
			function () { },
			'pronamic_post_expiration'
		);

		\add_settings_field(
			'pronamic_post_expiration_post_types',
			\__( 'Post types', 'pronamic-moneybird' ),
			function () {
				include __DIR__ . '/../admin/settings-field-post-types-support.php';
			},
			'pronamic_post_expiration',
			'pronamic_post_expiration_general'
		);
	}

	/**
	 * Admin menu.
	 * 
	 * @link https://developer.wordpress.org/reference/functions/add_options_page/
	 * @return void
	 */
	public function admin_menu() {
		\add_options_page(
			\__( 'Pronamic Post Expiration', 'pronamic-post-expiration' ),
			\__( 'Pronamic Post Expiration', 'pronamic-post-expiration' ),
			'manage_options',
			'pronamic_post_expiration',
			function () {
				include __DIR__ . '/../admin/page-settings.php';
			}
		);
	}
}
