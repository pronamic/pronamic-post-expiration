<?php
/**
 * Settings controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPressPostExpiration
 */

namespace Pronamic\WordPressPostExpiration;

/**
 * Settings controller class
 */
final class SettingsController {
	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_action( 'admin_init', [ $this, 'admin_init' ] );

		\add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Initialize.
	 * 
	 * @return void
	 */
	public function init() {
		\register_setting(
			'pronamic_post_expiration',
			'pronamic_post_expiration_config',
			/**
			 * Schema.
			 * 
			 * @link https://developer.wordpress.org/reference/functions/register_setting/
			 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/
			 */
			[
				'type'       => 'object',
				'properties' => [
					'post_types' => [
						'type'                 => 'object',
						'properties'           => [],
						'additionalProperties' => [
							'type'       => 'object',
							'properties' => [
								'post_status' => [
									'type'    => 'string',
									'default' => 'pronamic_expired',
								],
								'support'     => [
									'type'    => 'boolean',
									'default' => false,
								],
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Admin initialize.
	 * 
	 * @return void
	 */
	public function admin_init() {
		\add_settings_section(
			'pronamic_post_expiration_general',
			\__( 'General', 'pronamic-post-expiration' ),
			function (): void { },
			'pronamic_post_expiration'
		);

		\add_settings_field(
			'pronamic_post_expiration_config',
			\__( 'Post types', 'pronamic-post-expiration' ),
			function (): void {
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
			function (): void {
				include __DIR__ . '/../admin/page-settings.php';
			}
		);
	}
}
